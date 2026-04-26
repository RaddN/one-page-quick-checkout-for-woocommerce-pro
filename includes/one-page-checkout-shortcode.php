<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" category="" tags="" attribute="" terms="" template="" position="" product_label="" variation_label="" updating_selection_text="" show_images="" product_layout="" primary_color="" secondary_color="" border_radius="" spacing="" button_style=""]
if (!function_exists('onepaqucpro_sanitize_variation_attributes_for_cart')) {
    function onepaqucpro_sanitize_variation_attributes_for_cart($attributes)
    {
        $sanitized = array();

        foreach ((array) $attributes as $attribute_key => $attribute_value) {
            if ($attribute_value === '' || $attribute_value === null || is_array($attribute_value)) {
                continue;
            }

            $attribute_key = function_exists('onepaqucpro_normalize_attr_key')
                ? onepaqucpro_normalize_attr_key($attribute_key)
                : preg_replace('/[^\p{L}\p{N}_\-%]/u', '', trim((string) wp_unslash($attribute_key)));

            if ($attribute_key === '') {
                continue;
            }

            if (function_exists('onepaqucpro_normalize_attr_key') === false && strpos($attribute_key, 'attribute_') !== 0) {
                $attribute_key = 'attribute_' . $attribute_key;
            }

            $attribute_value = function_exists('onepaqucpro_normalize_attr_value')
                ? onepaqucpro_normalize_attr_value($attribute_value)
                : preg_replace('/[\r\n\t\0\x0B]+/', ' ', wp_strip_all_tags(trim((string) wp_unslash($attribute_value))));

            if ($attribute_value === '') {
                continue;
            }

            $sanitized[$attribute_key] = $attribute_value;
        }

        return $sanitized;
    }
}

function onepaqucpro_one_page_checkout_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'product_ids' => '',
        'category'    => '',
        'tags'        => '',
        'attribute'   => '',
        'terms'       => '',
        'template'    => 'product-table',
        'position'    => 'after_description',
        'template_position' => '',
        'product_label' => '',
        'variation_label' => '',
        'updating_selection_text' => '',
        'show_images' => 'no',
        'product_layout' => 'select_dropdown',
        'primary_color' => '',
        'secondary_color' => '',
        'border_radius' => '',
        'spacing' => '',
        'button_style' => '',
    ), $atts);

    if (function_exists('onepaqucpro_can_use_one_page_checkout_feature') && !onepaqucpro_can_use_one_page_checkout_feature()) {
        return function_exists('onepaqucpro_get_one_page_checkout_license_notice')
            ? onepaqucpro_get_one_page_checkout_license_notice()
            : '<div class="onepaqucpro-license-required">' . esc_html__('Pro version only. Please activate your license to use this feature.', 'one-page-quick-checkout-for-woocommerce-pro') . '</div>';
    }

    ob_start();

    // Collect product IDs from attributes if product_ids is empty
    $product_ids = array();

    if (!empty($atts['product_ids'])) {
        $product_ids = explode(',', $atts['product_ids']);
        $product_ids = array_map('trim', $product_ids);
    } else {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'publish',
        );

        $tax_query = array();

        if (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['category'])),
            );
        }

        if (!empty($atts['tags'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_tag',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['tags'])),
            );
        }

        if (!empty($atts['attribute']) && !empty($atts['terms'])) {
            $tax_query[] = array(
                'taxonomy' => 'pa_' . wc_sanitize_taxonomy_name($atts['attribute']),
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['terms'])),
            );
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);
        $product_ids = $query->posts;
    }

    if (empty($product_ids)) {
        return '<div class="rmenupro-one-page-checkout"><p>' . esc_html__('Please provide product IDs, category, tags, or attribute terms.', 'one-page-quick-checkout-for-woocommerce-pro') . '</p></div>';
    }

    $checkout_template = $atts['template'] === 'product-selection' ? 'product-selection' : '';
    $checkout_template_position = onepaqucpro_normalize_checkout_template_position(
        !empty($atts['template_position']) ? $atts['template_position'] : $atts['position']
    );

    if (class_exists('WooCommerce') && WC()->cart && get_option("onepaqucpro_checkout_widget_cart_empty", "1") === "1") {
        WC()->cart->empty_cart();
    }

    $auto_add_product_ids = $checkout_template === 'product-selection'
        ? array_slice($product_ids, 0, 1)
        : $product_ids;

    foreach ($auto_add_product_ids as $product_id) {
        $product_id = intval($product_id);
        if ($product_id > 0 && class_exists('WooCommerce') && WC()->cart && get_option("onepaqucpro_checkout_widget_cart_add", "1") === "1") {
            $product = wc_get_product($product_id);
            if ($product && $product->is_type('variable')) {
                $available_variations = onepaqucpro_get_validated_variations( $product );
                if (!empty($available_variations)) {
                    $variation_id = $available_variations[0]['variation_id'];
                    $variation = wc_get_product($variation_id);
                    if ($variation && $variation->is_purchasable()) {
                        $variation_attributes = !empty($available_variations[0]['attributes']) && is_array($available_variations[0]['attributes'])
                            ? onepaqucpro_sanitize_variation_attributes_for_cart($available_variations[0]['attributes'])
                            : onepaqucpro_sanitize_variation_attributes_for_cart($variation->get_variation_attributes());

                        WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation_attributes);
                    }
                }
            } else {
                // Check if the product is purchasable before adding to cart
                if ($product && $product->is_purchasable()) {
                    WC()->cart->add_to_cart($product_id);
                }
            }
        }
    }
?>
    <div class="rmenupro-one-page-checkout" id="checkout-popup">
        <?php
        // Include the checkout template based on the selected template
        if ($checkout_template === 'product-selection') {
            // Product selection is rendered inside the checkout form at the requested position.
        } elseif ($atts['template'] === 'product-table') {
            include plugin_dir_path(__FILE__) . '../templates/product-table-template.php';
        } elseif ($atts['template'] === 'product-list') {
            include plugin_dir_path(__FILE__) . '../templates/product-list-template.php';
        } elseif ($atts['template'] === 'product-single') {
            include plugin_dir_path(__FILE__) . '../templates/product-single-template.php';
        } elseif ($atts['template'] === 'product-slider') {
            include plugin_dir_path(__FILE__) . '../templates/product-slider-template.php';
        } elseif ($atts['template'] === 'product-accordion') {
            include plugin_dir_path(__FILE__) . '../templates/product-accordion-template.php';
        } elseif ($atts['template'] === 'product-tabs') {
            include plugin_dir_path(__FILE__) . '../templates/product-tabs-template.php';
        } else {
            include plugin_dir_path(__FILE__) . '../templates/pricing-table-template.php';
        }
        ?>

        <div class="opc-checkout-form-container">
            <?php
            onepaqucpro_display_one_page_checkout_form(array(
                'checkout_template' => $checkout_template,
                'checkout_template_position' => $checkout_template_position,
                'checkout_template_args' => array(
                    'product_ids' => $product_ids,
                    'atts' => $atts,
                ),
            ));
            ?>
        </div>
    </div>
<?php

    return ob_get_clean();
}
add_shortcode('plugincy_one_page_checkout', 'onepaqucpro_one_page_checkout_shortcode', 99999);

add_action('wp_ajax_onepaqucpro_product_selection_select_product', 'onepaqucpro_product_selection_select_product');
add_action('wp_ajax_nopriv_onepaqucpro_product_selection_select_product', 'onepaqucpro_product_selection_select_product');

function onepaqucpro_product_selection_select_product()
{
    check_ajax_referer('rmenupro-ajax-nonce', 'nonce');

    if (function_exists('onepaqucpro_can_use_one_page_checkout_feature') && !onepaqucpro_can_use_one_page_checkout_feature()) {
        wp_send_json_error(array(
            'message' => esc_html__('Pro version only. Please activate your license to use this feature.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => esc_html__('Cart is not available.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    $product_id = absint(isset($_POST['product_id']) ? wp_unslash($_POST['product_id']) : 0);
    $variation_id = absint(isset($_POST['variation_id']) ? wp_unslash($_POST['variation_id']) : 0);
    $raw_variations = isset($_POST['variations']) ? wp_unslash($_POST['variations']) : array();
    $variations = is_array($raw_variations) ? onepaqucpro_sanitize_variation_attributes_for_cart($raw_variations) : array();
    $product = $product_id ? wc_get_product($product_id) : false;

    if (!$product || !$product->is_purchasable()) {
        wp_send_json_error(array(
            'message' => esc_html__('Please choose a valid product.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    if ($product->is_type('variable')) {
        $variation = $variation_id ? wc_get_product($variation_id) : false;

        if (!$variation || (int) $variation->get_parent_id() !== (int) $product_id || !$variation->is_purchasable()) {
            wp_send_json_error(array(
                'message' => esc_html__('Please choose a valid variation.', 'one-page-quick-checkout-for-woocommerce-pro'),
            ));
        }

        foreach (onepaqucpro_sanitize_variation_attributes_for_cart($variation->get_variation_attributes()) as $attribute_key => $attribute_value) {
            if (empty($variations[$attribute_key])) {
                $variations[$attribute_key] = $attribute_value;
            }
        }
    }

    WC()->cart->empty_cart();

    $added = WC()->cart->add_to_cart($product_id, 1, $variation_id, $variations);

    if (!$added) {
        wp_send_json_error(array(
            'message' => esc_html__('The selected product could not be added to the cart.', 'one-page-quick-checkout-for-woocommerce-pro'),
        ));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'message' => esc_html__('Product selection updated.', 'one-page-quick-checkout-for-woocommerce-pro'),
    ));
}





// single product one page checkout

// Register: [onepaquc_checkout product_id="123" variation_id="456" qty="2" clear_cart="yes" auto_add="yes"]
add_action('init', function () {
    add_shortcode('onepaquc_checkout', function ($atts = []) {

        // --- Shortcode attributes ---
        $atts = shortcode_atts([
            // default enable auto add
            'auto_add'     => 'yes',   // yes|no
            'clear_cart'   => 'no',    // yes|no
            'product_id'   => 0,       // parent product (required to add)
            'variation_id' => 0,       // optional variation id
            'qty'          => 1,       // optional qty
        ], $atts, 'onepaquc_checkout');

        // Basic sanitization / normalization
        $auto_add     = in_array(strtolower($atts['auto_add']), ['yes', 'true', '1'], true);
        $clear_cart   = in_array(strtolower($atts['clear_cart']), ['yes', 'true', '1'], true);
        $product_id   = absint($atts['product_id']);
        $variation_id = absint($atts['variation_id']);
        $qty          = max(1, absint($atts['qty']));

        // --- Add to cart behavior ---
        if ( function_exists('WC') && !is_admin() && !wp_doing_ajax() ) {
            // Only do cart ops if we have a product_id and auto_add is enabled
            if ( $auto_add && $product_id > 0 ) {
                if ( $clear_cart && WC()->cart ) {
                    WC()->cart->empty_cart();
                }

                // If variation_id is given, add that specific variation.
                // Otherwise add the simple/parent product.
                if ( WC()->cart ) {
                    // NB: $variation is optional here (empty array ok if attributes aren’t needed)
                    $variation  = [];
                    $cart_item_data = [];

                    // Add and ignore errors silently (avoid breaking the page)
                    try {
                        // When adding a variation, Woo expects the parent variable product ID as $product_id,
                        // and the specific $variation_id you want to add.
                        if ( $variation_id > 0 ) {
                            WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation, $cart_item_data);
                        } else {
                            WC()->cart->add_to_cart($product_id, $qty, 0, $variation, $cart_item_data);
                        }
                    } catch ( \Throwable $e ) {
                        // You could log this if needed: error_log($e->getMessage());
                    }
                }
            }
        }

        // --- Render the checkout form ---
        if ( ! function_exists('onepaqucpro_display_one_page_checkout_form') ) {
            return '';
        }

        // Make sure we capture output (the function echoes)

        ob_start();
        onepaqucpro_display_one_page_checkout_form();
        $html = ob_get_clean();

        return $html;
    });
});
