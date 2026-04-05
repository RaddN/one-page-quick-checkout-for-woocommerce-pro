<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// ajaxhandler.php

// update cart content

add_action('wp_ajax_onepaqucpro_get_cart_content', 'onepaqucpro_get_cart_content');
add_action('wp_ajax_nopriv_onepaqucpro_get_cart_content', 'onepaqucpro_get_cart_content');
function onepaqucpro_get_cart_content()
{
    check_ajax_referer('get_cart_content_none', 'nonce');
    //get the values from the ajax request cart_icon: cartIcon, product_title_tag: productTitleTag, drawer_position: drawerPosition
    $cartIcon = isset($_POST['cart_icon']) ? sanitize_text_field(wp_unslash($_POST['cart_icon'])) : 'cart';
    $productTitleTag = isset($_POST['product_title_tag']) ? sanitize_text_field(wp_unslash($_POST['product_title_tag'])) : 'h2';
    $drawerPosition = isset($_POST['drawer_position']) ? sanitize_text_field(wp_unslash($_POST['drawer_position'])) : 'right';
    ob_start();

    // Use include to load the template from your plugin's directory
    onepaqucpro_cart($drawerPosition, $cartIcon, $productTitleTag);

    $cart_html = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success([
        'cart_html' => $cart_html,
        'cart_count' => WC()->cart->get_cart_contents_count()
    ]);
}

// update quantity

add_action('wp_ajax_onepaqucpro_update_cart_item_quantity', 'onepaqucpro_update_cart_item_quantity');
add_action('wp_ajax_nopriv_onepaqucpro_update_cart_item_quantity', 'onepaqucpro_update_cart_item_quantity');
function onepaqucpro_update_cart_item_quantity()
{
    check_ajax_referer('update_cart_item_quantity', 'nonce');
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? (int)sanitize_text_field(wp_unslash($_POST['quantity'])) : 0;

    if (WC()->cart->set_quantity($cart_item_key, $quantity)) {
        $cart = WC()->cart;

        // Get updated cart data
        $subtotal = wc_price($cart->get_subtotal());
        $total = wc_price($cart->get_total('raw'));
        $cart_count = $cart->get_cart_contents_count();

        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'cart_count' => $cart_count,
            'total' => $total
        ));
    } else {
        wp_send_json_error('Could not update quantity.');
    }
}

add_action('wp_ajax_onepaqucpro_update_cart_item_variation', 'onepaqucpro_update_cart_item_variation');
add_action('wp_ajax_nopriv_onepaqucpro_update_cart_item_variation', 'onepaqucpro_update_cart_item_variation');
function onepaqucpro_update_cart_item_variation()
{
    check_ajax_referer('update_cart_item_variation', 'nonce');

    if (!function_exists('onepaqucpro_cart_item_variation_switch_enabled') || !onepaqucpro_cart_item_variation_switch_enabled()) {
        wp_send_json_error(array(
            'message' => esc_html__('Variation switching is not enabled.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $cart_items = WC()->cart ? WC()->cart->get_cart() : array();

    if ($cart_item_key === '' || empty($cart_items[$cart_item_key])) {
        wp_send_json_error(array(
            'message' => esc_html__('The selected cart item could not be found.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $cart_item = $cart_items[$cart_item_key];
    $product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    $variable_product = $product_id ? wc_get_product($product_id) : false;

    if (!($variable_product instanceof WC_Product_Variable)) {
        wp_send_json_error(array(
            'message' => esc_html__('This cart item does not support variation switching.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $raw_variations = isset($_POST['variations']) ? wp_unslash($_POST['variations']) : array();
    $posted_variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
    $variation = array();

    if (is_array($raw_variations)) {
        foreach ($raw_variations as $attribute_key => $attribute_value) {
            if (!is_scalar($attribute_key) || !is_scalar($attribute_value) || $attribute_value === '') {
                continue;
            }

            $variation[onepaqucpro_normalize_attr_key((string) $attribute_key)] = onepaqucpro_normalize_attr_value((string) $attribute_value);
        }
    }

    if (empty($variation)) {
        wp_send_json_error(array(
            'message' => esc_html__('Please choose a valid variation before updating.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $data_store = WC_Data_Store::load('product');
    $matched_variation_id = $posted_variation_id > 0
        ? $posted_variation_id
        : (int) $data_store->find_matching_product_variation($variable_product, $variation);

    if ($matched_variation_id <= 0) {
        $matched_variation_id = (int) $data_store->find_matching_product_variation($variable_product, $variation);
    }

    $variation_product = $matched_variation_id > 0 ? wc_get_product($matched_variation_id) : false;
    if (!($variation_product instanceof WC_Product_Variation) || $variation_product->get_parent_id() !== $product_id) {
        wp_send_json_error(array(
            'message' => esc_html__('The selected variation is invalid.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    foreach ((array) $variation_product->get_variation_attributes() as $attribute_key => $attribute_value) {
        if ($attribute_value === '' || $attribute_value === null) {
            continue;
        }

        $normalized_key = onepaqucpro_normalize_attr_key($attribute_key);
        if (empty($variation[$normalized_key])) {
            $variation[$normalized_key] = onepaqucpro_normalize_attr_value($attribute_value);
        }
    }

    $current_variation_id = !empty($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
    $current_variation = function_exists('onepaqucpro_get_normalized_cart_item_variation_attributes')
        ? onepaqucpro_get_normalized_cart_item_variation_attributes($cart_item)
        : array();

    ksort($variation);
    ksort($current_variation);

    if ($matched_variation_id === $current_variation_id && $variation === $current_variation) {
        wp_send_json_success(array(
            'cart_item_key' => $cart_item_key,
            'message' => esc_html__('The selected variation is already in your cart.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $quantity = !empty($cart_item['quantity']) ? max(1, absint($cart_item['quantity'])) : 1;
    $cart_item_data = function_exists('onepaqucpro_get_cart_item_readd_data')
        ? onepaqucpro_get_cart_item_readd_data($cart_item)
        : array();

    wc_clear_notices();
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $matched_variation_id, $variation, $cart_item_data);

    if (!$passed_validation || !$variation_product->is_purchasable() || (!$variation_product->is_in_stock() && !$variation_product->backorders_allowed())) {
        $error_notices = wc_get_notices('error');
        $message = !empty($error_notices[0]['notice'])
            ? wp_strip_all_tags($error_notices[0]['notice'])
            : esc_html__('The selected variation cannot be added to the cart right now.', 'one-page-quick-checkout-for-woocommerce-pro');

        wc_clear_notices();

        wp_send_json_error(array(
            'message' => $message,
        ));
    }
    wc_clear_notices();

    $original_cart_item_data = $cart_item_data;
    $original_variation = !empty($cart_item['variation']) && is_array($cart_item['variation'])
        ? $cart_item['variation']
        : $current_variation;

    if (!WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_error(array(
            'message' => esc_html__('The original cart item could not be updated.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $new_cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $matched_variation_id, $variation, $cart_item_data);

    if (!$new_cart_item_key) {
        WC()->cart->add_to_cart($product_id, $quantity, $current_variation_id, $original_variation, $original_cart_item_data);

        wp_send_json_error(array(
            'message' => esc_html__('Could not update the selected variation. Please try again.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'cart_item_key' => $new_cart_item_key,
        'message' => esc_html__('Variation updated successfully.', 'one-page-quick-checkout-for-woocommerce-pro'),
    ));
}

add_action('wp_ajax_onepaqucpro_get_cart_item_variation_editor', 'onepaqucpro_get_cart_item_variation_editor');
add_action('wp_ajax_nopriv_onepaqucpro_get_cart_item_variation_editor', 'onepaqucpro_get_cart_item_variation_editor');
function onepaqucpro_get_cart_item_variation_editor()
{
    check_ajax_referer('get_cart_item_variation_editor', 'nonce');

    if (!function_exists('onepaqucpro_cart_item_variation_switch_enabled') || !onepaqucpro_cart_item_variation_switch_enabled()) {
        wp_send_json_success(array('html' => ''));
    }

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $context = isset($_POST['context']) ? sanitize_html_class(wp_unslash($_POST['context'])) : 'blocks-cart';
    $cart_items = WC()->cart ? WC()->cart->get_cart() : array();

    if ($cart_item_key === '' || empty($cart_items[$cart_item_key])) {
        wp_send_json_success(array('html' => ''));
    }

    $html = function_exists('onepaqucpro_get_cart_item_variation_editor_html')
        ? onepaqucpro_get_cart_item_variation_editor_html($cart_items[$cart_item_key], $cart_item_key, $context)
        : '';

    wp_send_json_success(array(
        'html' => $html,
    ));
}


// remove cart item
add_action('wp_ajax_onepaqucpro_remove_cart_item', 'onepaqucpro_handle_remove_cart_item');
add_action('wp_ajax_nopriv_onepaqucpro_remove_cart_item', 'onepaqucpro_handle_remove_cart_item');
function onepaqucpro_handle_remove_cart_item()
{
    check_ajax_referer('remove_cart_item', 'nonce');
    $cart_item_keys = array();
    if (isset($_POST['cart_item_key']) && is_array($_POST['cart_item_key'])) {
        $cart_item_keys = array_map('sanitize_text_field', wp_unslash($_POST['cart_item_key']));
    } elseif (isset($_POST['cart_item_key'])) {
        $cart_item_keys = array(sanitize_text_field(wp_unslash($_POST['cart_item_key'])));
    } else {
        wp_send_json_error('No cart item key provided.');
    }
    $sanitized_keys = array();
    foreach ($cart_item_keys as $key) {
        $sanitized_keys[] = sanitize_text_field(wp_unslash($key));
    }

    $failed_keys = array();

    foreach ($sanitized_keys as $key) {
        if (!WC()->cart->remove_cart_item($key)) {
            $failed_keys[] = $key;
        }
    }


    $cart = WC()->cart;

    // Get updated cart data
    $subtotal = wc_price($cart->get_subtotal());
    $total = wc_price($cart->get_total('raw'));

    if (empty($failed_keys)) {
        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'total' => $total
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Could not remove some items.',
            'failed_keys' => $failed_keys
        ));
    }
}

// Add AJAX handler for refreshing product list
add_action('wp_ajax_onepaqucpro_refresh_checkout_product_list', 'onepaqucpro_refresh_checkout_product_list');
add_action('wp_ajax_nopriv_onepaqucpro_refresh_checkout_product_list', 'onepaqucpro_refresh_checkout_product_list');

function onepaqucpro_refresh_checkout_product_list()
{
    // Check nonce for security
    check_ajax_referer('onepaqucpro_refresh_checkout_product_list', 'nonce');
    if (!isset($_POST['product_ids'])) {
        wp_die();
    }

    $product_ids = explode(',', sanitize_text_field(wp_unslash($_POST['product_ids'])));
    $product_ids = array_map('trim', $product_ids);

    ob_start();

    // Loop through each product ID
    foreach ($product_ids as $item_id) {
        $product_id = intval($item_id);
        $product = wc_get_product($product_id);

        if ($product) {
            $product_name = $product->get_name();
            $product_image = $product->get_image(array(60, 60), array('class' => 'one-page-checkout-product-image'));

            // Check if product is in cart
            $in_cart = false;
            $cart_item_key = '';

            foreach (WC()->cart->get_cart() as $key => $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $in_cart = true;
                    $cart_item_key = $key;
                    break;
                }
            }

            $checked = $in_cart ? 'checked' : '';
?>
            <li class="one-page-checkout-product-item" data-product-id="<?php echo esc_attr($product_id); ?>" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                <div class="one-page-checkout-product-container">
                    <label class="one-page-checkout-product-label">
                        <input type="checkbox" class="one-page-checkout-product-checkbox" value="<?php echo esc_attr($product_id); ?>" <?php echo esc_attr($checked); ?>>
                        <span class="one-page-checkout-product-image-wrap"><?php echo wp_kses_post($product_image); ?></span>
                        <span class="one-page-checkout-product-name"><?php echo esc_html($product_name); ?></span>
                        <span class="one-page-checkout-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    </label>
                </div>
            </li>
<?php
        }
    }

    $html = ob_get_clean();
    global $onepaquc_onepaquc_onepaqucpro_allowed_tags;

    echo wp_kses($html, $onepaquc_onepaquc_onepaqucpro_allowed_tags);
    wp_die();
}


add_action('wp_ajax_woocommerce_clear_cart', 'onepaqucpro_clear_cart');
add_action('wp_ajax_nopriv_woocommerce_clear_cart', 'onepaqucpro_clear_cart');

function onepaqucpro_clear_cart()
{
    WC()->cart->empty_cart();
    wp_send_json_success();
}


// add to cart

add_action('wp_ajax_onepaqucpro_ajax_add_to_cart', 'onepaqucpro_ajax_add_to_cart');
add_action('wp_ajax_nopriv_onepaqucpro_ajax_add_to_cart', 'onepaqucpro_ajax_add_to_cart');


function onepaqucpro_ajax_add_to_cart()
{
    check_ajax_referer('rmenupro-ajax-nonce', 'nonce');

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint(isset($_POST['product_id']) ? $_POST['product_id'] : 0));

    // Get default quantity from settings if quantity is not provided
    if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
        $default_qty = absint(get_option('rmenupro_add_to_cart_default_qty', '1'));
    } else {
        $default_qty = 1; // Default to 1 if premium feature is not available
    }

    if ($default_qty < 1) {
        $default_qty = 1;
    }

    // Use posted quantity if available, otherwise use default. Always keep quantity >= 1.
    $posted_quantity = isset($_POST['quantity']) ? absint(wp_unslash($_POST['quantity'])) : 0;
    $quantity = $posted_quantity > 0 ? $posted_quantity : $default_qty;

    $variation_id = empty($_POST['variation_id']) ? 0 : absint($_POST['variation_id']);
    $raw_variations = isset($_POST['variations']) ? wp_unslash($_POST['variations']) : array();
    $variations = is_array($raw_variations) ? array_map('sanitize_text_field', $raw_variations) : array();

    $product = wc_get_product($product_id);
    $product_status = $product ? $product->get_status() : get_post_status($product_id);
    $can_add_private_product = 'private' === $product_status && (current_user_can('read_private_products') || current_user_can('edit_post', $product_id));
    $can_add_by_status = in_array($product_status, array('publish'), true) || $can_add_private_product;

    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

    $added_to_cart = false;
    if ($passed_validation && $can_add_by_status) {
        $added_to_cart = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations);
    }

    if ($added_to_cart) {

        do_action('woocommerce_ajax_added_to_cart', $product_id);

        // Get product name for the message
        $product_name = $product ? $product->get_name() : '';

        // Get cart URL
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : WC()->cart->get_cart_url();

        // Get checkout URL
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : WC()->cart->get_checkout_url();
        
        // Render cart items
        $cart_items_html = "";
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_items = WC()->cart->get_cart();

        foreach ($cart_items as $cart_item_key => $cart_item) {
            ob_start();
            onepaqucpro_render_cart_drawer_item($cart_item_key, $cart_item, 'p');
            $cart_items_html .= ob_get_clean();
        }

        // Get redirect option
        $redirect_option = get_option('rmenupro_redirect_after_add', 'none');
        $redirect_url = 'none';

        if ($redirect_option === 'cart') {
            $redirect_url = $cart_url;
        } elseif ($redirect_option === 'checkout') {
            $redirect_url = $checkout_url;
        }

        $response = array(
            'success' => true,
            'product_name' => $product_name,
            'cart_url' => $cart_url,
            'checkout_url' => $checkout_url,
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_items_html' => $cart_items_html,
            'redirect' => $redirect_option !== 'none',
            'redirect_url' => $redirect_url
        );

        // Add fragments if Mini Cart Preview is selected
        if (get_option('rmenupro_add_to_cart_notification_style', 'default') === 'mini_cart') {
            ob_start();
            woocommerce_mini_cart();
            $mini_cart = ob_get_clean();

            $response['fragments']['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
            $response['cart_hash'] = WC()->cart->get_cart_hash();
        }

        wp_send_json($response);
    } else {
        $message = esc_html__('Error adding product to cart', 'one-page-quick-checkout-for-woocommerce-pro');
        if ('private' === $product_status && ! $can_add_private_product) {
            $message = esc_html__('This private product cannot be added to cart for the current user.', 'one-page-quick-checkout-for-woocommerce-pro');
        }

        $data = array(
            'success' => false,
            'error' => true,
            'message' => $message
        );

        wp_send_json($data);
    }

    wp_die();
}



// coupon ajax handler
add_action('wp_ajax_apply_coupon', 'onepaqucpro_apply_coupon');
add_action('wp_ajax_nopriv_apply_coupon', 'onepaqucpro_apply_coupon');

function onepaqucpro_apply_coupon()
{
    check_ajax_referer('apply-coupon', 'security');

    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

    // Apply coupon
    $cart = WC()->cart;
    $result = $cart->apply_coupon($coupon_code);

    if ($result) {
        // Get updated cart data
        $subtotal = wc_price($cart->get_subtotal());
        $discount_total = wc_price($cart->get_discount_total());
        $total = wc_price($cart->get_total('raw'));

        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'total' => $total
        ));
    } else {
        wp_send_json_error(array('message' => 'Invalid coupon code.'));
    }
}

add_action('wp_ajax_remove_coupon', 'onepaqucpro_remove_coupon');
add_action('wp_ajax_nopriv_remove_coupon', 'onepaqucpro_remove_coupon');

function onepaqucpro_remove_coupon()
{
    check_ajax_referer('apply-coupon', 'security');

    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

    // Remove coupon
    $cart = WC()->cart;
    $cart->remove_coupon($coupon_code);

    // Get updated cart data
    $subtotal = wc_price($cart->get_subtotal());
    $discount_total = wc_price($cart->get_discount_total());
    $total = wc_price($cart->get_total('raw'));

    wp_send_json_success(array(
        'subtotal' => $subtotal,
        'discount_total' => $cart->get_discount_total(),
        'total' => $total
    ));
}


/**
 * AJAX handler for getting all products quick view data
 */
function onepaqucpro_get_all_products_quick_view() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rmenu_quick_view_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Get product IDs from the request
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
    
    if (empty($product_ids)) {
        wp_send_json_error(array('message' => 'No product IDs provided'));
    }
    
    $products_data = array();
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            continue; // Skip if product doesn't exist
        }
        
        // Get product images
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();
        
        // Add featured image first
        $featured_image_id = $product->get_image_id();
        if ($featured_image_id) {
            array_unshift($attachment_ids, $featured_image_id);
        }
        
        // If no images, add placeholder
        if (empty($attachment_ids)) {
            $images[] = array(
                'id'    => 0,
                'src'   => wc_placeholder_img_src(),
                'thumb' => wc_placeholder_img_src('thumbnail'),
                'full'  => wc_placeholder_img_src('full'),
                'alt'   => esc_html__('Placeholder', 'one-page-quick-checkout-for-woocommerce-pro')
            );
        } else {
            foreach ($attachment_ids as $attachment_id) {
                $image_src = wp_get_attachment_image_src($attachment_id, 'woocommerce_single');
                $thumb_src = wp_get_attachment_image_src($attachment_id, 'woocommerce_thumbnail');
                $full_src  = wp_get_attachment_image_src($attachment_id, 'full');
                
                $images[] = array(
                    'id'    => $attachment_id,
                    'src'   => $image_src ? $image_src[0] : wc_placeholder_img_src(),
                    'thumb' => $thumb_src ? $thumb_src[0] : wc_placeholder_img_src('thumbnail'),
                    'full'  => $full_src ? $full_src[0] : wc_placeholder_img_src('full'),
                    'alt'   => get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
                );
            }
        }
        
        // Get product categories
        $categories_html = '';
        $categories = get_the_terms($product_id, 'product_cat');
        if ($categories && !is_wp_error($categories)) {
            $category_links = array();
            foreach ($categories as $category) {
                $category_links[] = '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
            }
            $categories_html = implode(', ', $category_links);
        }
        
        // Get product brands (if using a brands plugin)
        $brands_html = '';
        if (taxonomy_exists('product_brand')) {
            $brands = get_the_terms($product_id, 'product_brand');
            if ($brands && !is_wp_error($brands)) {
                $brand_links = array();
                foreach ($brands as $brand) {
                    $brand_links[] = '<a href="' . esc_url(get_term_link($brand)) . '">' . esc_html($brand->name) . '</a>';
                }
                $brands_html = implode(', ', $brand_links);
            }
        }
        
        // Get product tags
        $tags_html = '';
        $tags = get_the_terms($product_id, 'product_tag');
        if ($tags && !is_wp_error($tags)) {
            $tag_links = array();
            foreach ($tags as $tag) {
                $tag_links[] = '<a href="' . esc_url(get_term_link($tag)) . '">' . esc_html($tag->name) . '</a>';
            }
            $tags_html = implode(', ', $tag_links);
        }
        
        // Compile product data
        $products_data[$product_id] = array(
            'id'                   => $product_id,
            'title'                => $product->get_name(),
            'permalink'            => $product->get_permalink(),
            'price_html'           => $product->get_price_html(),
            'excerpt'              => $product->get_short_description(),
            'rating_html'          => wc_get_rating_html($product->get_average_rating()),
            'type'                 => $product->get_type(),
            'sku'                  => $product->get_sku(),
            'images'               => $images,
            'is_in_stock'          => $product->is_in_stock(),
            'is_purchasable'       => $product->is_purchasable(),
            'min_purchase_quantity' => $product->get_min_purchase_quantity(),
            'max_purchase_quantity' => $product->get_max_purchase_quantity(),
            'brands_html'          => $brands_html,
            'categories_html'      => $categories_html,
            'tags_html'            => $tags_html
        );
    }
    
    // Send the response
    wp_send_json_success($products_data);
}
add_action('wp_ajax_rmenu_get_all_products_quick_view', 'onepaqucpro_get_all_products_quick_view');
add_action('wp_ajax_nopriv_rmenu_get_all_products_quick_view', 'onepaqucpro_get_all_products_quick_view');
