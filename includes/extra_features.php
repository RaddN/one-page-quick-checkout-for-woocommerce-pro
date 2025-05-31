<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add product image to WooCommerce checkout page cart items
 */
function onepaquc_add_product_image_to_checkout_cart_items($product_name, $cart_item, $cart_item_key)
{
    if (get_option("rmenu_add_img_before_product") !== "1") {
        return $product_name;
    }
    // Get the product
    $product = $cart_item['data'];

    // Get product thumbnail
    $thumbnail = $product->get_image(array(50, 50));

    // Return the image followed by the product name
    return '<div class="checkout-product-item"><div class="checkout-product-image">' . $thumbnail . '</div><div class="checkout-product-name">' . $product_name . '</div></div>';
}
add_filter('woocommerce_cart_item_name', 'onepaquc_add_product_image_to_checkout_cart_items', 10, 3);

// add a random product in cart

if (get_option('rmenu_at_one_product_cart', 1)) {
    add_action('template_redirect', 'onepaquc_add_random_product_if_cart_empty');
}

function onepaquc_add_random_product_if_cart_empty()
{

    // If cart is empty
    if (WC()->cart->is_empty()) {

        // Get one random product ID
        $random_product = wc_get_products(array(
            'status'    => 'publish',
            'limit'     => 1,
            'orderby'   => 'rand',
            'return'    => 'ids',
            'type'      => 'simple', // Change to 'variable' if needed
        ));

        if (!empty($random_product)) {
            WC()->cart->add_to_cart($random_product[0], 1);
        }
    }
}

if (get_option('rmenu_disable_cart_page', 0)) {
    add_action('template_redirect', 'disable_cart_page_redirect');
    function disable_cart_page_redirect()
    {
        if (is_cart()) {
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }
}

if (get_option('rmenu_link_product', 0)) {
    add_filter('woocommerce_cart_item_name', 'link_product_name_on_checkout', 10, 3);
    function link_product_name_on_checkout($product_name, $cart_item, $cart_item_key)
    {
        // Only apply on the checkout page
        if (is_checkout()) {
            $product = $cart_item['data'];
            $product_link = get_permalink($product->get_id());
            $product_name = sprintf('<a href="%s">%s</a>', esc_url($product_link), $product_name);
        }
        return $product_name;
    }
}




/**
 * Add variation selection buttons to product archive pages
 */
/**
 * Add variation selection buttons to product archive pages using woocommerce_loop_add_to_cart_link
 */
if (get_option('rmenu_variation_show_archive', 1)) {
    add_filter('woocommerce_loop_add_to_cart_link', 'add_variation_buttons_to_loop', 10, 2);
}

function add_variation_buttons_to_loop($link, $product)
{
    // Only proceed if this is a variable product
    if ($product->is_type('variable')) {
        // Get available variations
        $available_variations = $product->get_available_variations();

        ob_start(); // Start output buffering

        echo '<div class="archive-variations-container">';

        // Loop through all variations and create a button for each
        foreach ($available_variations as $variation) {
            $variation_id = $variation['variation_id'];

            // Create a readable title from the attributes
            $variation_title = array();
            foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                $taxonomy = str_replace('attribute_', '', $attribute_name);
                $term_name = $attribute_value;

                // If it's a taxonomy attribute, get the term name
                if (taxonomy_exists($taxonomy)) {
                    $term = get_term_by('slug', $attribute_value, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $term_name = $term->name;
                    }
                }

                if (!empty($term_name)) {
                    $variation_title[] = $term_name;
                }
            }

            $button_text = implode(' / ', $variation_title);

            // Create the button with data-id attribute
            echo '<button type="button" class="variation-button" data-id="' . esc_attr($variation_id) . '">' . esc_html($button_text) . '</button>';
        }

        // Hidden input to store the selected variation ID
        echo '<input type="hidden" class="variation_id" value="">';

        echo '</div>'; // .archive-variations-container

        // Simple JS to update the variation_id when a button is clicked
?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.variation-button').click(function() {
                    var variation_id = $(this).data('id');
                    $(this).closest('.archive-variations-container').find('.variation_id').val(variation_id);
                    $(this).addClass('selected').siblings().removeClass('selected');
                });
            });
        </script>
        <style>
            .archive-variations-container {
                margin-top: 10px;
                margin-bottom: 10px;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .variation-button {
                background-color: #f7f7f7;
                border: 1px solid #ddd;
                padding: 5px 10px;
                border-radius: 3px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .variation-button:hover {
                background-color: #eaeaea;
            }

            .variation-button.selected {
                background-color: #4CAF50;
                color: white;
                border-color: #4CAF50;
            }
        </style>
        <?php

        // Append the variations container before the add to cart link
        return ob_get_clean() . $link; // Return the variations and the link
    }

    return $link; // Return the original link for non-variable products
}

if (get_option('rmenu_wc_hide_select_option', 1)) {
    // Add custom CSS for .product_type_variable in the footer
    add_action('wp_footer', 'add_custom_css_for_variable_products');
    function add_custom_css_for_variable_products()
    {
        if (is_product() || is_shop() || is_product_category() || is_product_tag()) {
            echo '<style>
            .product_type_variable {
                display: none !important;
            }
        </style>';
        }
    }
}




// Change WooCommerce checkout layout based on onpage_checkout_layout option
add_action('wp_head', 'apply_checkout_layout_styles');

function apply_checkout_layout_styles()
{
    $layout = get_option('onpage_checkout_layout', 'two_column');


    switch ($layout) {
        case 'one_column':
            echo '
                <style>
                @media (min-width: 768px) {
                    .onepagecheckoutwidget  form.checkout.woocommerce-checkout {
                        flex-direction: column;
                    }
                    .onepagecheckoutwidget  div#customer_details,.onepagecheckoutwidget  div#order_review {
                        width: 100% !important;
                        margin: 0 auto;
                    }
                }
                </style>
                ';
            break;

        case 'product_first':
            echo '
                <style>
                @media (min-width: 768px) {
                    .onepagecheckoutwidget  form.checkout.woocommerce-checkout{
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                    }
                    .onepagecheckoutwidget .woocommerce-checkout .col2-set {
                            width: 100% !important;
                            margin: 0 !important;
                        }
                    .onepagecheckoutwidget  .woocommerce-checkout>table.shop_table.woocommerce-checkout-review-order-table {
                            width: 100% !important;
                            float: none !important;
                            margin: 0 auto !important;
                        }
                    .onepagecheckoutwidget  tr.cart_item td.product-name {
                        display: flex;
                        padding: 12px !important;
                        gap: 10px;
                    }
                    .onepagecheckoutwidget  tr td.product-total,.onepagecheckoutwidget  tr.cart-subtotal td, .onepagecheckoutwidget  tr.order-total td {
                        padding-left: 20px !important;
                    }
                    .onepagecheckoutwidget  .payment_box p {
                        padding: 20px;
                    }
                    
                }
                </style>
                ';
        ?>
            <script>
                jQuery(document).ready(function($) {
                    function moveOrderReview() {
                        if ($('.onepagecheckoutwidget  #order_review_heading').length && $('.onepagecheckoutwidget  table.shop_table.woocommerce-checkout-review-order-table').length && !$('.onepagecheckoutwidget  .woocommerce-checkout>table.shop_table.woocommerce-checkout-review-order-table').length) {
                            var orderReviewHeading = $('.onepagecheckoutwidget  #order_review_heading').detach();
                            var orderReview = $('.onepagecheckoutwidget  table.shop_table.woocommerce-checkout-review-order-table').detach();
                            $('.onepagecheckoutwidget  .woocommerce-checkout').prepend(orderReview).prepend(orderReviewHeading);
                            orderReview.css('margin-bottom', '30px');
                            orderReviewHeading.css('margin-bottom', '10px');
                        }
                    }

                    // Initial load
                    moveOrderReview();

                    // After AJAX updates
                    $(document.body).on('updated_checkout', function() {
                        moveOrderReview();
                    });
                });
            </script>
<?php
            break;

        case 'two_column':
        default:
            echo '
                <style>
                @media (min-width: 768px) {
                    .onepagecheckoutwidget form.woocommerce-checkout{
                        display: flex;
                        gap: 40px;
                    }
                    .onepagecheckoutwidget #customer_details,.onepagecheckoutwidget #order_review {
                        width: 48% !important;
                        margin: 0 !important;
                    }
                    .onepagecheckoutwidget #order_review_heading {
                        display: none;
                    }
                }
                </style>
                ';
            break;
    }
}

// Alternative method using body class for more targeted CSS
add_filter('body_class', 'add_checkout_layout_body_class');

function add_checkout_layout_body_class($classes)
{
    if (is_checkout()) {
        $layout = get_option('onpage_checkout_layout', 'two_column');
        $classes[] = 'checkout-layout-' . $layout;
    }
    return $classes;
}
