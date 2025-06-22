<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly



/**
 * One Page Quick Checkout for WooCommerce Pro
 * 
 * Adds a checkbox to product settings and displays checkout form directly on product page
 * when enabled, creating a streamlined purchasing experience.
 */

/**
 * Add One Page Checkout checkbox to product type options
 */
function onepaqucpro_add_one_page_checkout_to_product_type_options($product_type_options)
{
    $product_type_options['one_page_checkout'] = array(
        'id'            => '_one_page_checkout',
        'wrapper_class' => '',
        'label'         => __('One Page Checkout', 'one-page-quick-checkout-for-woocommerce-pro'),
        'description'   => __('Enable one page checkout for this product', 'one-page-quick-checkout-for-woocommerce-pro'),
        'default'       => 'no'
    );


    wp_nonce_field('onepaqucpro_save_meta', 'onepaqucpro_nonce');

    return $product_type_options;
}
add_filter('product_type_options', 'onepaqucpro_add_one_page_checkout_to_product_type_options');

/**
 * Save One Page Checkout option
 */
function onepaqucpro_save_one_page_checkout_option($post_id)
{
    // Check if our nonce is set and valid
    check_ajax_referer('onepaqucpro_save_meta', 'onepaqucpro_nonce');

    $is_one_page_checkout = isset($_POST['_one_page_checkout']) ? 'yes' : 'no';
    update_post_meta($post_id, '_one_page_checkout', $is_one_page_checkout);
}
add_action('woocommerce_process_product_meta', 'onepaqucpro_save_one_page_checkout_option', 10);

