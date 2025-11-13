<?php

/**
 * Plugin Name: One Page Quick Checkout for WooCommerce Pro
 * Plugin URI:  https://plugincy.com/one-page-quick-checkout-for-woocommerce-pro/
 * Description: Enhance WooCommerce with popup checkout, cart drawer, and flexible checkout templates to boost conversions.
 * Version: 1.1.3
 * Author: plugincy
 * Author URI: https://plugincy.com
 * license: GPL2
 * Text Domain: one-page-quick-checkout-for-woocommerce-pro
 * Requires Plugins: woocommerce
 */


// Check if the free version is installed and active, deactivate it
add_action('plugins_loaded', function () {
    // Try to find the free plugin by slug, regardless of directory name
    $plugins = get_plugins();
    foreach ($plugins as $plugin_file => $plugin_data) {
        if (
            strpos($plugin_file, 'one-page-quick-checkout-for-wooCommerce.php') !== false &&
            $plugin_file !== plugin_basename(__FILE__)
        ) {
            if (is_plugin_active($plugin_file)) {
                deactivate_plugins($plugin_file);
                // Redirect after deactivation
                add_action('admin_init', function () {
                    wp_redirect(admin_url('admin.php?page=onepaqucpro_cart'));
                    exit;
                });
                add_action('admin_notices', function () use ($plugin_data) {
                    echo '<div class="notice notice-warning is-dismissible"><p>';
                    esc_html_e('One Page Quick Checkout for WooCommerce (free version) has been deactivated. Please use only the Pro version.', 'one-page-quick-checkout-for-woocommerce-pro');
                    echo '</p></div>';
                });
            }
        }
    }
}, 1);


if (! defined('ABSPATH')) exit; // Exit if accessed directly

define('ONEPAQUCPRO_PLUGIN_URL', plugin_dir_url(__FILE__));

define("RMENUPRO_VERSION", "1.1.3");

require_once plugin_dir_path(__FILE__) . 'admin/license-tab.php';

// Include the admin notice file
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';
}

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// include one page checkout shortcode
require_once plugin_dir_path(__FILE__) . 'includes/one-page-checkout-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/add-to-cart-button.php';
require_once plugin_dir_path(__FILE__) . 'includes/class_helper.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/checkout-form-block.php';

global $onepaqucpro_checkoutformfields, $onepaqucpro_productpageformfields, $onepaqucpro_rcheckoutformfields, $onepaqucpro_string_settings_fields;

require_once plugin_dir_path(__FILE__) . 'includes/global-values.php';
require_once plugin_dir_path(__FILE__) . 'includes/quickview.php';
require_once plugin_dir_path(__FILE__) . 'includes/checkout_form_customize.php';
require_once plugin_dir_path(__FILE__) . 'includes/analytics.php';

$onepaqucpro_string_settings_fields = [
    "onepaqucpro_editor",
    "onepaqucpro_checkout_position",
    "onepaqucpro_checkout_cart_empty",
    "onepaqucpro_checkout_enable",
    "onepaqucpro_checkout_enable_all",
    "onepaqucpro_checkout_layout",
    "onepaqucpro_checkout_cart_add",
    "onepaqucpro_checkout_widget_cart_empty",
    "onepaqucpro_checkout_widget_cart_add",
    "onepaqucpro_checkout_hide_cart_button",
    "rmenupro_quantity_control",
    "rmenupro_at_one_product_cart",
    "rmenupro_disable_cart_page",
    "rmenupro_force_login",
    "rmenupro_link_product",
    "rmenupro_allow_analytics",
    "rmenupro_remove_product",
    "rmenupro_add_img_before_product",
    "rmenupro_add_direct_checkout_button",
    "rmenupro_enable_custom_add_to_cart",
    "rmenupro_wc_checkout_guest_enabled",
    "rmenupro_wc_checkout_mobile_optimize",
    "rmenupro_wc_direct_checkout_position",
    "rmenu_wc_direct_checkout_single_position",
    "rmenupro_variation_show_archive",
    "rmenu_variation_layout",
    "rmenu_show_variation_title",
    "rmenupro_wc_hide_select_option",
    "txt-direct-checkout",
    "rmenupro_wc_checkout_color",
    "rmenupro_add_to_cart_bg_color",
    "rmenupro_wc_checkout_text_color",
    "rmenupro_wc_checkout_custom_css",
    "rmenupro_add_to_cart_text_color",
    "rmenupro_add_to_cart_hover_bg_color",
    "rmenupro_add_to_cart_hover_text_color",
    "rmenupro_add_to_cart_border_radius",
    "rmenupro_add_to_cart_font_size",
    "rmenupro_add_to_cart_width",
    "rmenupro_add_to_cart_custom_width",
    "rmenupro_add_to_cart_custom_css",
    "rmenupro_add_to_cart_icon",
    "rmenupro_add_to_cart_icon_position",
    "rmenupro_add_to_cart_catalog_display",
    "rmenupro_wc_checkout_style",
    "rmenupro_add_to_cart_style",
    "rmenupro_wc_checkout_icon",
    "rmenupro_wc_checkout_icon_position",
    "rmenupro_wc_checkout_method",
    "rmenupro_wc_clear_cart",
    "rmenupro_wc_one_click_purchase",
    "rmenupro_wc_add_confirmation",
    "rmenupro_enable_ajax_add_to_cart",
    "rmenupro_add_to_cart_default_qty",
    "rmenupro_show_quantity_archive",
    "rmenupro_redirect_after_add",
    "rmenupro_add_to_cart_animation",
    "rmenupro_add_to_cart_notification_style",
    "rmenupro_add_to_cart_success_message",
    "rmenupro_show_view_cart_link",
    "rmenupro_add_to_cart_notification_duration",
    "rmenupro_show_checkout_link",
    "rmenupro_sticky_add_to_cart_mobile",
    "rmenupro_mobile_add_to_cart_text",
    "rmenupro_mobile_button_size",
    "rmenupro_hide_on_mobile_options",
    "rmenupro_mobile_icon_only",
    "rmenupro_add_to_cart_loading_effect",
    "rmenupro_disable_btn_out_of_stock",
    "rmenupro_force_button_css",
    "rmenupro_enable_quick_view",
    "rmenupro_quick_view_button_text",
    "rmenupro_quick_view_button_position",
    "rmenupro_quick_view_display_type",
    "rmenupro_quick_view_modal_size",
    "rmenupro_quick_view_custom_width",
    "rmenupro_quick_view_custom_height",
    "rmenupro_quick_view_enable_lightbox",
    "rmenupro_quick_view_loading_effect",
    "rmenupro_quick_view_button_style",
    "rmenupro_quick_view_button_color",
    "rmenupro_quick_view_text_color",
    "rmenupro_quick_view_button_icon",
    "rmenupro_quick_view_icon_position",
    "rmenupro_quick_view_custom_css",
    "rmenupro_quick_view_ajax_add_to_cart",
    "rmenupro_quick_view_direct_checkout",
    "rmenupro_quick_view_mobile_optimize",
    "rmenupro_quick_view_close_on_add",
    "rmenupro_quick_view_keyboard_nav",
    "rmenupro_quick_view_preload",
    "rmenupro_quick_view_enable_cache",
    "rmenupro_quick_view_cache_expiration",
    "rmenupro_quick_view_lazy_load",
    "rmenupro_quick_view_details_text",
    "rmenupro_quick_view_close_text",
    "rmenupro_quick_view_prev_text",
    "rmenupro_quick_view_next_text",
    "rmenupro_quick_view_track_events",
    "rmenupro_quick_view_event_category",
    "rmenupro_quick_view_event_action",
    "rmenupro_quick_view_load_scripts",
    "rmenupro_quick_view_theme_compat",
    "onepaqucpro_trust_badges_enabled",
    "onepaqucpro_trust_badge_position",
    "onepaqucpro_trust_badge_style",
    "show_custom_html",
    "rmenu_enable_sticky_cart",
    "rmenu_cart_checkout_behavior",
    "rmenu_cart_top_position",
    "rmenu_cart_left_position",
    "rmenu_cart_bg_color",
    "rmenu_cart_text_color",
    "rmenu_cart_hover_bg",
    "rmenu_cart_hover_text",
    "rmenu_cart_border_radius",
    "rmenu_show_cart_icon",
    "rmenu_show_cart_count",
    "rmenu_show_cart_total",
    "rmenu_cart_animation",
];

// Enqueue scripts and styles
function onepaqucpro_cart_enqueue_scripts()
{

    $checkout_page_id = wc_get_page_id('checkout');

    // Check if checkout page exists and has [woocommerce_checkout] shortcode
    if ($checkout_page_id === -1) {
        // Create a new checkout page if it doesn't exist
        $new_checkout_id = wp_insert_post([
            'post_title'   => esc_html__('Checkout', 'one-page-quick-checkout-for-woocommerce-pro'),
            'post_content' => '[woocommerce_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
        if ($new_checkout_id && !is_wp_error($new_checkout_id)) {
            update_option('woocommerce_checkout_page_id', $new_checkout_id);
        }
    }

    wp_enqueue_style('rmenupro-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css', array(), "1.1.3");
    if (get_option('onepaqucpro_checkout_layout', 'two_column') === "two_column") {
        wp_enqueue_style('checkout-form-two-column', plugin_dir_url(__FILE__) . 'assets/css/checkout-form-two-column.css', array(), "1.1.3");
    }
    wp_enqueue_script('rmenupro-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), "1.1.3", true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), "1.1.3", true);
    $direct_checkout_behave = [
        'rmenupro_wc_checkout_method' => get_option('rmenupro_wc_checkout_method', 'direct_checkout'),
        'rmenupro_wc_clear_cart' => get_option('rmenupro_wc_clear_cart', 0),
        'rmenupro_wc_one_click_purchase' => get_option('rmenupro_wc_one_click_purchase', 0),
        'rmenupro_wc_add_confirmation' => get_option('rmenupro_wc_add_confirmation', 0),
    ];
    // Localize script for AJAX URL and WooCommerce cart variables
    wp_localize_script('cart-script', 'onepaqucpro_wc_cart_params', array(
        'ajax_url' => esc_url(admin_url('admin-ajax.php')),
        'get_cart_content_none' => esc_js(wp_create_nonce('get_cart_content_none')),
        'update_cart_item_quantity' => esc_js(wp_create_nonce('update_cart_item_quantity')),
        'remove_cart_item' => esc_js(wp_create_nonce('remove_cart_item')),
        'rmenu_ajax_nonce' => esc_js(wp_create_nonce('rmenu-ajax-nonce')),
        'onepaqucpro_refresh_checkout_product_list' => esc_js(wp_create_nonce('onepaqucpro_refresh_checkout_product_list')),
        'get_variations_nonce' => esc_js(wp_create_nonce('get_variations_nonce')), // Add this line
        'direct_checkout_behave' => $direct_checkout_behave,
        'checkout_url' => wc_get_checkout_url(),
        'cart_url'     => wc_get_cart_url(),
        'nonce' => esc_js(wp_create_nonce('rmenupro-ajax-nonce')),
    ));
    // Retrieve the onepaqucpro_editor value
    $onepaqucpro_editor_value = get_option('onepaqucpro_editor', '');
    $currency_symbol = get_woocommerce_currency_symbol();

    $plugincy_all_settings = [];
    $others_settings = [
        'onepaqucpro_checkout_fields',
        'rmenupro_show_quick_checkout_by_types',
        'rmenupro_show_quick_checkout_by_page',
        'rmenupro_add_to_cart_by_types',
        'rmenupro_quick_view_content_elements',
        'rmenupro_show_quick_view_by_types',
        'rmenupro_show_quick_view_by_page',
        'onepaqucpro_my_trust_badges_items',
        'checkout_form_setup',
        'onepaqucpro_trust_badge_custom_html',
    ];
    foreach ($GLOBALS['onepaqucpro_string_settings_fields'] as $field) {
        $plugincy_all_settings[$field] = get_option($field, '');
    }
    foreach ($others_settings as $field) {
        $plugincy_all_settings[$field] = get_option($field, []);
    }

    // Localize the script with the onepaqucpro_editor value
    wp_localize_script('rmenupro-cart-script', 'onepaqucpro_rmsgValue', array(
        'rmsgEditor' => $onepaqucpro_editor_value,
        'checkout_url' => wc_get_checkout_url(),
        'apply_coupon' => esc_js(wp_create_nonce('apply-coupon')),
        'currency_symbol' => $currency_symbol,
        'plugincy_all_settings' => $plugincy_all_settings,
        'ajax_url' => esc_url(admin_url('admin-ajax.php'))
    ));
}
add_action('wp_enqueue_scripts', 'onepaqucpro_cart_enqueue_scripts', 20);

add_action('admin_enqueue_scripts', 'onepaqucpro_cart_admin_styles');

// Enqueue the admin stylesheet only for this settings page
function onepaqucpro_cart_admin_styles($hook)
{
    if ($hook === 'toplevel_page_onepaqucpro_cart') {
        wp_enqueue_style('onepaqucpro_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.1.3");
        wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . 'assets/css/select2.min.css', array(), "1.1.3");
        wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'assets/js/select2.min.js', array('jquery'), "1.1.3", true);
    }
    wp_enqueue_style('onepaqucpro_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-documentation.css', array(), "1.1.3");
    wp_enqueue_script('rmenupro-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-documentation.js', array('jquery'), "1.1.3", true);
    wp_enqueue_editor();
}

// add shortcode
// if (get_option('onepaqucpro_api_key') && get_option('onepaqucpro_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajaxhandler.php';
require_once plugin_dir_path(__FILE__) . 'includes/label-change.php';



// }else{
//     require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
// }

function onepaqucpro_editor_script()
{
    if (wp_script_is('onepaquc_editor_script', 'enqueued')) {
        return;
    }

    wp_enqueue_script(
        'onepaquc_editor_script',
        plugin_dir_url(__FILE__) . 'includes/blocks/editor.js',
        array('wp-blocks', 'wp-element', 'wp-edit-post', 'wp-dom-ready', 'wp-plugins'),
        '1.1.3',
        true
    );
}
add_action('enqueue_block_editor_assets', 'onepaqucpro_editor_script');


require_once plugin_dir_path(__FILE__) . 'includes/cart-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/popup-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/documentation.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/plugincy-cart-blocks.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/one-page-checkout.php';

// checkout popup form

function onepaqucpro_rmenupro_checkout_popup($isonepagewidget = false)
{
    // Return if this is the cart or checkout page
    if (is_cart() || !$GLOBALS['is_checkout_by_plugincy']) {
        return;
    }

    // Return if the current page content already has a WooCommerce checkout form
    global $post;
    if ($post && has_shortcode($post->post_content, 'woocommerce_checkout')) {
        return;
    }
?>
    <div class="checkout-popup <?php echo $isonepagewidget ? 'onepagecheckoutwidget' : ''; ?>" data-isonepagewidget="<?php echo esc_attr($isonepagewidget); ?>" style="<?php echo $isonepagewidget ? 'display: block; position: unset; transform: unset; box-shadow: none; background: unset; width: 100%; max-width: 100%; height: 100%;overflow: hidden;' : 'display:none'; ?>;">
        <?php
        onepaqucpro_rmenupro_checkout($isonepagewidget);
        ?>
    </div>
<?php
}

require_once plugin_dir_path(__FILE__) . 'admin/product_edit_page_setup.php';


function onepaqucpro_display_checkout_on_single_product()
{
    // Only run on single product pages
    if (!is_product()) {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaqucpro_rmenupro_checkout_popup');
        }
        return;
    }

    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product->is_purchasable() || (!$product->is_in_stock() && !$product->is_on_backorder())) {
        return;
    }

    if (!$product || !is_a($product, 'WC_Product')) {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaqucpro_rmenupro_checkout_popup');
        }
        return;
    }

    $one_page_checkout = get_post_meta($product_id, '_one_page_checkout', true);
    $isallproduct_checkout_enable = get_option("onepaqucpro_checkout_enable_all", 0);

    if ($one_page_checkout === 'yes' || $isallproduct_checkout_enable) {

        if (!WC()->cart->is_empty() && get_option("onepaqucpro_checkout_cart_empty", "1") === "1") {
            WC()->cart->empty_cart();
        }

        if (get_option("onepaqucpro_checkout_cart_add", "1") === "1") {
            if ($product->is_type('variable')) {
                $available_variations = $product->get_available_variations();
                if (!empty($available_variations)) {
                    $variation = $available_variations[0];
                    $variation_id = $variation['variation_id'];
                    $variation_attributes = $variation['attributes'];
                    WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation_attributes);
                }
            } elseif ($product->is_type('grouped')) {
                $children_ids = $product->get_children();
                foreach ($children_ids as $child_id) {
                    $child_product = wc_get_product($child_id);
                    if ($child_product && $child_product->is_purchasable() && $child_product->is_in_stock()) {
                        WC()->cart->add_to_cart($child_id, 1);
                    }
                }
            } else {
                // Simple, downloadable, etc.
                WC()->cart->add_to_cart($product_id, 1);
            }
        } else {
            add_action('wp_footer', 'onepaqucpro_cart_add_disabled_notice');
        }

        add_action('wp_enqueue_scripts', 'onepaqucpro_add_checkout_inline_styles', 99);
        if (get_option("onepaqucpro_checkout_enable", "1") === "1") {

            add_filter('woocommerce_product_tabs', 'onepaqucpro_add_checkout_tab_to_product_page');

            add_action('woocommerce_after_single_product_summary', 'onepaqucpro_display_one_page_checkout_form',  get_option("onepaqucpro_checkout_position", '9'));
            // Fallback hooks for themes that don't use the standard hook

            add_action('wp', function () {
                // Product tabs area hooks
                add_action('woocommerce_product_tabs', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));
                add_action('woocommerce_before_product_tabs', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));
                add_action('woocommerce_after_product_tabs', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));

                // Related products
                add_action('woocommerce_output_related_products', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));
                add_action('woocommerce_before_related_products', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));
                add_action('woocommerce_after_related_products', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));

                // After single product
                add_action('woocommerce_after_single_product', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));

                // WooCommerce content hooks (broader scope)
                add_action('woocommerce_after_main_content', 'onepaqucpro_display_one_page_checkout_form', get_option("onepaqucpro_checkout_position", '9'));

                add_action('wp_footer', 'onepaqucpro_display_one_page_checkout_form', 10);
            });
        }
        if (get_option("onepaqucpro_checkout_hide_cart_button") === "1") {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            // add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
            //     return false;
            // }, 10, 2);
            add_action('wp_head', function () {
                echo '<style>
                    .quantity, 
                    button.single_add_to_cart_button.button {
                        display: none !important;
                    }
                </style>';
            });
        }
    } else {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaqucpro_rmenupro_checkout_popup');
        }
    }
}

add_action('wp', 'onepaqucpro_display_checkout_on_single_product', 99);

/**
 * Display admin notice when "Add to Cart on Page Load" is disabled
 * Shows in wp_footer for logged-in administrators on product pages
 */
function onepaqucpro_cart_add_disabled_notice()
{
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }

    // Only show on single product pages
    if (!is_product()) {
        return;
    }

?>
    <div id="onepaquc-admin-notice" style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        max-width: 400px;
        background: linear-gradient(135deg, #fff5e6 0%, #ffffff 100%);
        border-left: 5px solid #f39c12;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        padding: 20px;
        z-index: 999999;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        animation: slideInRight 0.5s ease-out;
    ">
        <button onclick="document.getElementById('onepaquc-admin-notice').style.display='none'" style="
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 20px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            line-height: 1;
        " title="Dismiss">×</button>

        <div style="display: flex; align-items: start; gap: 12px;">
            <div style="
                background: #f39c12;
                color: white;
                border-radius: 50%;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 20px;
                font-weight: bold;
            ">⚠</div>

            <div style="flex: 1;">
                <h4 style="
                    margin: 0 0 8px 0;
                    font-size: 16px;
                    font-weight: 600;
                    color: #333;
                ">Add to Cart is Disabled</h4>

                <p style="
                    margin: 0 0 12px 0;
                    font-size: 13px;
                    line-height: 1.5;
                    color: #555;
                ">
                    <strong>One page checkout is enabled </strong> for this product but Current product will not be added to cart automatically.
                    <strong>One Page Checkout will not respond properly.</strong>
                </p>

                <div style="
                    background: rgba(243, 156, 18, 0.1);
                    border-radius: 4px;
                    padding: 10px;
                    font-size: 12px;
                    line-height: 1.4;
                    color: #666;
                    border-left: 3px solid #f39c12;
                ">
                    <strong style="color: #f39c12;">📌 Action Required:</strong><br>
                    Enable <em>"Add to Cart on Page Load"</em> in<br>
                    <strong>onpage checkout -> One Page Checkout Settings</strong>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        #onepaquc-admin-notice button:hover {
            color: #333 !important;
        }
    </style>
<?php
}

function onepaqucpro_checkout_already_rendered(): bool
{
    return defined('ONEPAQUC_CHECKOUT_RENDERED') && ONEPAQUC_CHECKOUT_RENDERED === 1;
}



/**
 * Display the checkout form
 */
function onepaqucpro_display_one_page_checkout_form(): bool
{

    if (onepaqucpro_checkout_already_rendered() || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return false;
    }


?>
    <div class="one-page-checkout-container onepagecheckoutwidget" id="checkout-popup" data-isonepagewidget="true">
        <h2>Checkout</h2>
        <p class="one-page-checkout-description"><?php echo get_option('txt-complete_your_purchase') ? esc_attr(get_option('txt-complete_your_purchase')) : 'Complete your purchase using the form below.'; ?></p>
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>
    <?php

    // Mark as rendered for the rest of this request
    if (!defined('ONEPAQUC_CHECKOUT_RENDERED')) {
        define('ONEPAQUC_CHECKOUT_RENDERED', 1);
    }
    return true;
}


function onepaqucpro_add_checkout_tab_to_product_page($tabs)
{
    // If it's already printed somewhere, don't add the tab
    if (onepaqucpro_checkout_already_rendered()) {
        return $tabs;
    }

    // Add checkout tab as the first tab
    $new_tabs = array();

    // Add checkout tab first
    $new_tabs['checkout'] = array(
        'title'    => esc_html__('Checkout', 'one-page-quick-checkout-for-woocommerce-pro'),
        'priority' => 5, // Lower number = higher priority (appears first)
        'callback' => 'onepaqucpro_display_one_page_checkout_form'
    );

    // Add existing tabs after checkout
    foreach ($tabs as $key => $tab) {
        $tab['priority'] = $tab['priority'] + 10; // Push other tabs down
        $new_tabs[$key] = $tab;
    }

    return $new_tabs;
}

function onepaqucpro_add_checkout_inline_styles()
{
    // Make sure style is enqueued before adding inline styles
    if (wp_style_is('rmenupro-cart-style', 'enqueued')) {
        wp_add_inline_style('rmenupro-cart-style', '.checkout-button-drawer {display: none !important; } a.checkout-button-drawer-link { display: flex !important; }');
    }
}

// if current page contains the shortcode plugincy_one_page_checkout
function onepaqucpro_check_shortcode_and_enqueue_styles()
{
    if (is_page() && has_shortcode(get_post()->post_content, 'plugincy_one_page_checkout')) {
        add_action('wp_enqueue_scripts', 'onepaqucpro_add_checkout_inline_styles', 99);
    }
}
add_action('wp', 'onepaqucpro_check_shortcode_and_enqueue_styles', 99);

/**
 * Replace the default quantity display with quantity controls in checkout
 */
function onepaqucpro_custom_quantity_input_on_checkout($html, $cart_item, $cart_item_key)
{

    // Get current quantity
    $quantity = $cart_item['quantity'];
    $new_html = '<strong class="product-quantity">× ' . esc_attr($quantity) . '</strong>';
    if (get_option("rmenupro_quantity_control", "1") === "1") {
        // Build custom quantity input
        $new_html = '<div class="checkout-quantity-control">';
        $new_html .= '<button type="button" class="checkout-qty-btn checkout-qty-minus" data-cart-item="' . esc_attr($cart_item_key) . '">-</button>';
        $new_html .= '<input type="text" name="cart[' . esc_attr($cart_item_key) . '][qty]" class="checkout-qty-input" value="' . esc_attr($quantity) . '" min="1" step="1" size="4">';
        $new_html .= '<button type="button" class="checkout-qty-btn checkout-qty-plus" data-cart-item="' . esc_attr($cart_item_key) . '">+</button>';
        $new_html .= '</div>';
    }
    if (get_option("rmenupro_remove_product", "1") === "1") {
        // remove button
        $remove_button = ' <a class="remove-item-checkout" data-cart-item="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('Remove this item', 'one-page-quick-checkout-for-woocommerce-pro') . '"><svg style="width: 12px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg></a>';
        $new_html .= $remove_button;
    }
    return $new_html;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'onepaqucpro_custom_quantity_input_on_checkout', 10, 3);


/**
 * Force checkout mode across all pages
 * 
 * Forces WooCommerce to treat all pages as checkout pages
 * Useful for custom checkout implementations
 * 
 * @param bool $is_checkout Original checkout status
 * @return bool Always returns true
 */
add_filter('woocommerce_is_checkout', 'onepaqucpro_force_woocommerce_checkout_mode', 999);

function onepaqucpro_force_woocommerce_checkout_mode($is_checkout)
{

    // Don't affect wp-admin (except AJAX calls)
    if (is_admin() && !(defined('DOING_AJAX') && DOING_AJAX)) {
        return $is_checkout;
    }

    if ($is_checkout) {
        return $is_checkout;
    }

    // (1) Current page has [plugincy_one_page_checkout] shortcode
    $has_opc_shortcode = onepaqucpro_page_has_shortcode('plugincy_one_page_checkout');

    // (2) Global setting: popup checkout
    $checkout_method   = get_option('rmenupro_wc_checkout_method', 'direct_checkout');
    $is_popup_checkout = ($checkout_method === 'popup_checkout');

    // (3) Single product page & (global enable-all OR per-product enabled)
    $enable_all_opc    = (bool) get_option('onepaqucpro_checkout_enable_all', 0);
    $is_single_product = function_exists('is_product') && is_product();

    $per_product_enabled = false;
    if ($is_single_product && !$enable_all_opc) {
        // Resolve current product ID safely
        $product_id = 0;
        if (function_exists('get_queried_object_id')) {
            $product_id = (int) get_queried_object_id();
        }
        if (!$product_id && isset($GLOBALS['post']->ID)) {
            $product_id = (int) $GLOBALS['post']->ID;
        }
        if ($product_id) {
            $per_product_enabled = (bool) get_post_meta($product_id, '_one_page_checkout', true);
        }
    }

    // Apply your OR conditions
    if (
        $has_opc_shortcode
        || $is_popup_checkout
        || ($is_single_product && ($enable_all_opc || $per_product_enabled))
    ) {

        $GLOBALS['is_checkout_by_plugincy'] = true;

        return true;
    }

    return $is_checkout;
}

/**
 * Check if the current queried page content contains a shortcode.
 */
function onepaqucpro_page_has_shortcode($shortcode_tag)
{
    if (empty($shortcode_tag)) return false;

    // Check global $post first
    if (isset($GLOBALS['post']->post_content) && function_exists('has_shortcode')) {
        if (has_shortcode($GLOBALS['post']->post_content, $shortcode_tag)) {
            return true;
        }
    }

    // Fallback: check queried object content
    if (function_exists('get_queried_object')) {
        $obj = get_queried_object();
        if (!empty($obj->post_content) && function_exists('has_shortcode')) {
            if (has_shortcode($obj->post_content, $shortcode_tag)) {
                return true;
            }
        }
    }

    return false;
}


require_once plugin_dir_path(__FILE__) . 'includes/extra_features.php';
require_once plugin_dir_path(__FILE__) . 'includes/quick_checkout_button.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/buy-now-button-block.php';
require_once plugin_dir_path(__FILE__) . 'includes/trusted-badge.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/plugincy-cart-widget.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/one-page-checkout.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/elementor-category.php';


add_filter('woocommerce_checkout_fields', 'onepaqucpro_remove_required_checkout_fields');

function onepaqucpro_remove_required_checkout_fields($fields)
{
    if (get_option('onepaqucpro_checkout_fields')) {
        $removed_fields = get_option('onepaqucpro_checkout_fields');

        foreach ($fields as $key => $field_group) {
            foreach ($field_group as $field_key => $field) {
                // Check if the field_key contains any of the removed fields
                foreach ($removed_fields as $removed_field) {
                    if (strpos($field_key, $removed_field) !== false) {
                        $fields[$key][$field_key]['required'] = false;
                    }
                }
            }
        }
    }
    return $fields;
}

// if (get_option('onepaqucpro_checkout_fields')) {
//     $checkout_fields = get_option('onepaqucpro_checkout_fields');
//     foreach ($checkout_fields as $field) {
//         if (isset($onepaqucpro_rcheckoutformfields[$field])) {
//             $selector = $onepaqucpro_rcheckoutformfields[$field]['selector'];
//             $custom_css .= "{$selector} { display: none !important; }\n";
//         }
//     }
// }


// add_settings_link
function onepaqucpro_add_settings_link($links)
{
    if (!is_array($links)) {
        $links = [];
    }
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=onepaqucpro_cart')) . '">' . esc_html__('Settings', 'one-page-quick-checkout-for-woocommerce-pro') . '</a>';
    $links[] = $settings_link;
    return $links;
}


// add settings button after deactivate button in plugins page

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'onepaqucpro_add_settings_link');


add_action('wp_head', function () {
    if (isset($_GET['hide_header_footer']) && $_GET['hide_header_footer'] == '1') {
        echo '<style>
            div#ast-scroll-top, div#wpadminbar, header, .site-header, #masthead, footer, .site-footer, #colophon,.rmenupro-cart,iframe,.woocommerce form .form-row::after, .woocommerce form .form-row::before, .woocommerce-page form .form-row::after, .woocommerce-page form .form-row::before,aside {
                display: none !important;
            }
            html {
                margin: 0 !important;
                padding-bottom: 100px;
            }
            .ast-container {
                padding: 0 !important;
            }
            .woocommerce-info {
                margin: 0 !important;
            }
            .form-row.place-order {
                position: fixed;
                bottom: 0;
                background: #fff;
                width: 100%;
                left: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 0 !important;
                box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            }
            .form-row.place-order p.order-total-price {
                margin: 0 !important;
            }
            button#place_order {
                width: max-content !important;
                margin: 0 !important;
            }
            order-total-price {
                display: flex;
                flex-direction: column;
            }
            .order-total-price bdi {
                font-weight: bold;
                font-size: 20px;
            }
        </style>';
        // Add JS to append ?hide_header_footer=1 to all links
    ?>
        <script>
            const elementsToHide = [
                'div#ast-scroll-top',
                'div#wpadminbar',
                'header',
                '.site-header',
                '#masthead',
                'footer',
                '.site-footer',
                '#colophon',
                '.rmenupro-cart',
                'iframe',
                'aside'
            ];

            // Function to hide elements
            function hideElements() {
                elementsToHide.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(element => {
                        element.style.setProperty('display', 'none', 'important');
                        element.style.setProperty('visibility', 'hidden', 'important');
                        element.style.setProperty('opacity', '0', 'important');
                        // Optionally remove the element entirely
                        // element.remove();
                    });
                });
            }

            // Run immediately
            hideElements();

            // Run on DOM ready
            document.addEventListener('DOMContentLoaded', hideElements);

            // Run after page load (for late-loading elements)
            window.addEventListener('load', hideElements);

            // Create a MutationObserver to watch for dynamically added elements
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                // Check if the added node matches our selectors
                                elementsToHide.forEach(selector => {
                                    if (node.matches && node.matches(selector)) {
                                        node.style.setProperty('display', 'none', 'important');
                                        node.style.setProperty('visibility', 'hidden', 'important');
                                        node.style.setProperty('opacity', '0', 'important');
                                    }
                                    // Also check children of the added node
                                    const children = node.querySelectorAll ? node.querySelectorAll(selector) : [];
                                    children.forEach(child => {
                                        child.style.setProperty('display', 'none', 'important');
                                        child.style.setProperty('visibility', 'hidden', 'important');
                                        child.style.setProperty('opacity', '0', 'important');
                                    });
                                });
                            }
                        });
                    }
                });
            });

            // Start observing when body is available
            function startObserver() {
                const targetNode = document.body || document.documentElement;
                if (targetNode) {
                    observer.observe(targetNode, {
                        childList: true,
                        subtree: true
                    });
                } else {
                    // If neither body nor documentElement exists, wait
                    document.addEventListener('DOMContentLoaded', () => {
                        observer.observe(document.body || document.documentElement, {
                            childList: true,
                            subtree: true
                        });
                    });
                }
            }

            startObserver();

            // Add CSS to prevent flash of unstyled content
            const style = document.createElement('style');
            style.textContent = `
                ${elementsToHide.join(', ')} {
                    display: none !important;
                    visibility: hidden !important;
                    opacity: 0 !important;
                }
            `;
            document.head.appendChild(style);

            // Disable right-click context menu
            document.addEventListener('contextmenu', function(event) {
                event.preventDefault();
            });

            // Define a function to append ?hide_header_footer=1 to all links
            function appendHideHeaderFooterParam() {
                // Store current screen width & height in localStorage
                localStorage.setItem('screen_width', window.innerWidth);
                localStorage.setItem('screen_height', window.innerHeight);

                var links = document.querySelectorAll('a[href]');
                links.forEach(function(link) {
                    var href = link.getAttribute('href');
                    // Ignore anchors, mailto, tel, javascript, and already set param
                    if (
                        href.indexOf('mailto:') === 0 ||
                        href.indexOf('tel:') === 0 ||
                        href.indexOf('#') === 0 ||
                        href.indexOf('hide_header_footer=1') !== -1
                    ) {
                        return;
                    }

                    // Add param
                    if (href.indexOf('?') !== -1) {
                        href += '&hide_header_footer=1';
                    } else {
                        href += '?hide_header_footer=1';
                    }
                    link.setAttribute('href', href);
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                appendHideHeaderFooterParam();
            });

            // Also run after every AJAX completion (jQuery required)
            if (window.jQuery) {
                jQuery(document).ajaxComplete(function() {
                    appendHideHeaderFooterParam();
                    hideElements();
                });
            }
        </script>
    <?php
    }
});











// Add this after your plugin initialization
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    add_action('plugins_loaded', 'onepaqucpro_init_updater');
}

function onepaqucpro_init_updater()
{
    if (class_exists('onepaqucpro_License_Manager')) {
        global $onepaqucpro_License_Manager;

        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', function ($transient) use ($onepaqucpro_License_Manager) {
            return onepaqucpro_check_for_plugin_updates($transient, $onepaqucpro_License_Manager);
        });

        add_filter('plugins_api', function ($result, $action, $args) use ($onepaqucpro_License_Manager) {
            return onepaqucpro_plugin_api_call($result, $action, $args, $onepaqucpro_License_Manager);
        }, 10, 3);

        add_action('upgrader_process_complete', function ($upgrader_object, $options) use ($onepaqucpro_License_Manager) {
            onepaqucpro_clear_cache_after_update($upgrader_object, $options, $onepaqucpro_License_Manager);
        }, 10, 2);

        add_action('admin_notices', array($onepaqucpro_License_Manager, 'show_license_notices'));
    }
}

function onepaqucpro_check_for_plugin_updates($transient, $license_manager)
{
    if (empty($transient->checked)) {
        return $transient;
    }

    if (!$license_manager->is_license_valid_cached()) {
        return $transient;
    }

    $plugin_file = plugin_basename(__FILE__); // This will automatically get the correct path
    $current_version = defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.1.3';

    $update_info = $license_manager->check_for_updates();

    if ($update_info && version_compare($current_version, $update_info->new_version, '<')) {
        $transient->response[$plugin_file] = (object) array(
            'slug' => dirname($plugin_file),
            'plugin' => $plugin_file,
            'new_version' => $update_info->new_version,
            'url' => isset($update_info->homepage) ? $update_info->homepage : 'https://plugincy.com/',
            'package' => isset($update_info->download_link) ? $update_info->download_link : '',
            'tested' => /*isset($update_info->tested) ? $update_info->tested :*/ get_bloginfo('version'),
            'requires_php' => isset($update_info->requires_php) ? $update_info->requires_php : '7.0',
            'compatibility' => new stdClass()
        );
    }

    return $transient;
}


function onepaqucpro_plugin_api_call($result, $action, $args, $license_manager)
{
    if ($action !== 'plugin_information') {
        return $result;
    }

    $plugin_slug = dirname(plugin_basename(__FILE__)); // Auto-detect plugin slug

    if (!isset($args->slug) || $args->slug !== $plugin_slug) {
        return $result;
    }

    if (!$license_manager->is_license_valid_cached()) {
        return $result;
    }

    $license_key = get_option('onepaquc_license_key', '');
    $version_info = $license_manager->get_version_info($license_key);

    if ($version_info) {
        // Unserialize sections if they exist and are serialized
        $sections = array(
            'description' => 'Professional One Page Quick Checkout for WooCommerce Pro solution for WooCommerce.',
            'changelog' => 'Various improvements and bug fixes.'
        );

        if (isset($version_info->sections)) {
            if (is_string($version_info->sections)) {
                // If sections is a serialized string, unserialize it
                $unserialized_sections = @unserialize($version_info->sections);
                if ($unserialized_sections !== false && is_array($unserialized_sections)) {
                    $sections = array_merge(is_array($sections) ? $sections : [], is_array($unserialized_sections) ? $unserialized_sections : []);
                }
            } elseif (is_object($version_info->sections)) {
                // If sections is already an object, convert to array
                $sections = array_merge(is_array($sections) ? $sections : [], (array)$version_info->sections);
            } elseif (is_array($version_info->sections)) {
                // If sections is already an array
                $sections = array_merge(is_array($sections) ? $sections : [], $version_info->sections);
            }
        }

        // Handle banners
        $banners = array();
        if (isset($version_info->banners)) {
            if (is_string($version_info->banners)) {
                // If banners is a serialized string, unserialize it
                $unserialized_banners = @unserialize($version_info->banners);
                if ($unserialized_banners !== false && is_array($unserialized_banners)) {
                    $banners = $unserialized_banners;
                }
            } elseif (is_object($version_info->banners)) {
                // If banners is already an object, convert to array
                $banners = (array)$version_info->banners;
            } elseif (is_array($version_info->banners)) {
                // If banners is already an array
                $banners = $version_info->banners;
            }
        }

        // Handle screenshots - WordPress expects array of URLs with numeric keys
        $base_url = "https://ps.w.org/dynamic-ajax-product-filters-for-woocommerce/assets/";
        $default_screenshots = array(
            "1" => "Filters Demo 1",
            "2" => "Filters Demo 2",
            "3" => "Filters Demo 3",
            "4" => "Filters Demo 4 - Mobile View",
            "5" => "Filters Demo 5 - Mobile View",
            "6" => "Form Manage Settings",
            "7" => "Form Style Settings",
            "8" => "Plugin Advance Settings"
        );

        // Get captions from server or use defaults
        $screenshot_captions = $default_screenshots;
        if (isset($version_info->screenshots)) {
            if (is_string($version_info->screenshots)) {
                $unserialized_screenshots = @unserialize($version_info->screenshots);
                if ($unserialized_screenshots !== false && is_array($unserialized_screenshots)) {
                    $screenshot_captions = $unserialized_screenshots;
                }
            } elseif (is_object($version_info->screenshots)) {
                $screenshot_captions = (array)$version_info->screenshots;
            } elseif (is_array($version_info->screenshots)) {
                $screenshot_captions = $version_info->screenshots;
            }
        }

        // Also add screenshot captions to sections for better display
        if (!empty($screenshot_captions)) {
            $screenshot_section = "<ol>";
            foreach ($screenshot_captions as $number => $caption) {
                $screenshot_section .= "<li>";
                $screenshot_section .= "<a href='{$base_url}screenshot-{$number}.png' target='_blank'><img class='screenshots' src='{$base_url}screenshot-{$number}.png' alt='{$caption}'></a><p>{$caption}</p>";
                $screenshot_section .= "</li>";
            }
            $screenshot_section .= "</ol>";
            $sections['screenshots'] = $screenshot_section;
        }

        return (object) array(
            'name' => 'One Page Quick Checkout for WooCommerce Pro',
            'slug' => $plugin_slug,
            'version' => $version_info->new_version,
            'author' => '<a href="https://plugincy.com">Plugincy</a>',
            'homepage' => 'https://plugincy.com/',
            'requires' => isset($version_info->requires) ? $version_info->requires : '5.0',
            'tested' => /*isset($version_info->tested) ? $version_info->tested :*/ get_bloginfo('version'),
            'requires_php' => isset($version_info->requires_php) ? $version_info->requires_php : '7.0',
            'contributors' => array(
                'plugincy' => array(
                    'profile' => 'https://profiles.wordpress.org/plugincy/',
                    'avatar' => 'https://secure.gravatar.com/avatar/ee0db1e8766d68a4bc66e91b4098310d9604ca7670ac9662c15915c517662b39',
                    'display_name' => 'Plugincy'
                )
            ),
            'sections' => $sections,
            'banners' => $banners,
            'download_link' => isset($version_info->download_link) ? $version_info->download_link : ''
        );
    }

    return $result;
}

function onepaqucpro_clear_cache_after_update($upgrader_object, $options, $license_manager)
{
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        $plugin_file = plugin_basename(__FILE__);

        if (isset($options['plugins']) && in_array($plugin_file, $options['plugins'])) {
            $license_manager->clear_all_cache();
        }
    }
}
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    // Add this temporarily for testing - remove after testing
    add_action('admin_init', function () {
        if (isset($_GET['force_check_updates']) && $_GET['force_check_updates'] === '1') {
            delete_site_transient('update_plugins');
            wp_redirect(admin_url('plugins.php'));
            exit;
        }
    });
}



// Force login before WooCommerce checkout
function onepaqucpro_force_login_before_checkout()
{
    // Check if the force login option is enabled and user is not logged in
    if (get_option('rmenupro_force_login', '0') == '1' && !is_user_logged_in()) {

        // Check if we're on the checkout page
        if (is_checkout() && !is_wc_endpoint_url()) {
            // Remove the default checkout form
            remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
            remove_action('woocommerce_checkout_billing', 'woocommerce_checkout_billing', 10);
            remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_shipping', 10);

            // Add custom login form instead
            add_action('woocommerce_before_checkout_form', 'onepaqucpro_show_login_form_instead_checkout', 5);

            // Hide the checkout form
            add_filter('woocommerce_checkout_show_terms', '__return_false');
            add_action('woocommerce_checkout_init', 'onepaqucpro_hide_checkout_form_when_not_logged_in');
        }
    }
}
add_action('template_redirect', 'onepaqucpro_force_login_before_checkout');

// Hide checkout form and show login form
function onepaqucpro_hide_checkout_form_when_not_logged_in()
{
    if (get_option('rmenupro_force_login', '0') == '1' && !is_user_logged_in()) {
        // Remove checkout form elements
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
        remove_action('woocommerce_checkout_billing', 'woocommerce_checkout_billing', 10);
        remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_shipping', 10);
        remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
    }
}

// Display login form on checkout page
function onepaqucpro_show_login_form_instead_checkout()
{
    if (get_option('rmenupro_force_login', '0') == '1' && !is_user_logged_in()) {
        echo '<div class="woocommerce-info">';
        echo '<p>' . esc_html__('You must be logged in to proceed with checkout.', 'one-page-quick-checkout-for-woocommerce-pro') . '</p>';
        echo '</div>';

        // Display login form
        woocommerce_login_form(array(
            'message' => esc_html__('Please login to continue with your order.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'redirect' => wc_get_checkout_url(),
            'hidden' => false
        ));

        // Add registration form if registration is enabled
        if (get_option('users_can_register')) {
            echo '<div class="woocommerce-register-wrapper" style="margin-top: 20px;">';
            echo '<h3>' . esc_html__('Register', 'one-page-quick-checkout-for-woocommerce-pro') . '</h3>';
            woocommerce_register_form();
            echo '</div>';
        }
    }
}

function onepaqucpro_hide_checkout_css_when_not_logged_in()
{
    if (get_option('rmenupro_force_login', '0') == '1' && !is_user_logged_in() && is_checkout()) {
    ?>
        <style>
            form.woocommerce-checkout,
            .woocommerce-form-coupon-toggle {
                display: none !important;
            }
        </style>
<?php
    }
}
add_action('wp_head', 'onepaqucpro_hide_checkout_css_when_not_logged_in');

if (get_option("rmenu_enable_sticky_cart", 0)) {
    function onepaqucpro_display_cart()
    {
        if (class_exists('WooCommerce')) {
            echo do_shortcode('[plugincy_cart drawer="right" cart_icon="cart" product_title_tag="p" position="fixed"]');
        }
    }

    add_action('wp_footer', 'onepaqucpro_display_cart');
}




class onepaqucpro_cart_analytics_main
{
    private $analytics;

    public function __construct()
    {
        // Initialize analytics with the correct plugin file path
        $this->analytics = new onepaqucpro_cart_anaylytics(
            '03',
            'https://plugincy.com/wp-json/product-analytics/v1',
            "1.1.3",
            'One Page Quick Checkout for WooCommerce',
            __FILE__ // Pass the main plugin file
        );

        add_action('admin_footer',  array($this->analytics, "add_deactivation_feedback_form"));

        // Plugin hooks
        add_action('init', array($this, 'init'));
        if (get_option('rmenupro_allow_analytics', 1)) {
            add_action('admin_init', array($this, 'admin_init'));
        }

        // Handle deactivation feedback AJAX
        add_action('wp_ajax_onepaqucpro_send_deactivation_feedback', array($this, 'handle_deactivation_feedback'));

        // Also enqueue script in admin to ensure AJAX variables are available
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }



    public function init()
    {
        // Any initialization code
    }

    public function admin_init()
    {
        // Send analytics data on first activation or weekly
        $this->maybe_send_analytics();
    }

    private function maybe_send_analytics()
    {
        $last_sent = get_option('onepaquc_analytics_last_sent', 0);
        $week_ago = strtotime('-1 week');

        if ($last_sent < $week_ago) {
            $this->analytics->send_tracking_data();
            update_option('onepaquc_analytics_last_sent', time());
        }
    }

    /**
     * Enqueue admin scripts to ensure AJAX URL is available
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only on plugins page
        if ($hook !== 'plugins.php') {
            return;
        }

        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');
    }

    public function handle_deactivation_feedback()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'deactivation_feedback')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            wp_die();
        }

        // Get and sanitize reason
        $reason = isset($_POST['reason']) ? sanitize_text_field(wp_unslash($_POST['reason'])) : 'no-reason-provided';

        // Send deactivation data through analytics class
        $result = $this->analytics->send_deactivation_data($reason);

        // Send response
        if ($result) {
            wp_send_json_success(array('message' => 'Feedback sent successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send feedback'));
        }

        wp_die();
    }
}

new onepaqucpro_cart_analytics_main();





add_action('wp_enqueue_scripts', function () {
    wp_add_inline_script('jquery-core', "
        (function() {
            function isDebugMode() {
                return new URLSearchParams(window.location.search).get('plugincydebug') === 'true';
            }
            
            window.plugincydebugLog = function() {
                if (isDebugMode() && console && console.log) {
                    console.log.apply(console, arguments);
                }
            };
        })();
    ");
});


















/**
 * WooCommerce URL Parameter Add to Cart Handler
 * 
 * Handles adding products to cart via URL parameters
 * Supports: Simple, Variable, Grouped, and other product types
 * 
 * URL Parameters:
 * - onepaqucpro_add-to-cart: Product ID (required)
 * 
 * - onepaqucpro_quantity: Quantity (optional, default: 1)
 * - onepaqucpro_variation_id: Variation ID for variable products (optional)
 * - onepaqucpro_attribute_*: Variation attributes (optional)
 * 
 * Example URLs:
 * Simple Product: ?onepaqucpro_add-to-cart=76&onepaqucpro_quantity=2
 * Variable Product: ?onepaqucpro_add-to-cart=76&onepaqucpro_variation_id=1745&onepaqucpro_quantity=1
 * With Attributes: ?onepaqucpro_add-to-cart=76&onepaqucpro_variation_id=1745&onepaqucpro_attribute_pa_color=blue
 */

add_action('template_redirect', 'onepaqucpro_handle_url_add_to_cart', 20);

function onepaqucpro_handle_url_add_to_cart()
{
    if (!isset($_GET['onepaqucpro_add-to-cart'])) {
        return;
    }

    // Ensure cart is available
    if (null === WC()->cart) {
        wc_load_cart();
    }

    $product_id = absint($_GET['onepaqucpro_add-to-cart']);
    if ($product_id <= 0) {
        wc_add_notice(__('Invalid product ID.', 'woocommerce'), 'error');
        return;
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wc_add_notice(__('Product not found.', 'woocommerce'), 'error');
        return;
    }
    if (!$product->is_purchasable()) {
        wc_add_notice(__('This product cannot be purchased.', 'woocommerce'), 'error');
        return;
    }

    $quantity = isset($_GET['onepaqucpro_quantity']) ? max(1, absint($_GET['onepaqucpro_quantity'])) : 1;

    $variation_id   = isset($_GET['onepaqucpro_variation_id']) ? absint($_GET['onepaqucpro_variation_id']) : 0;
    $variation      = array();
    $cart_item_data = array();

    // Variable product handling
    if ($product->is_type('variable')) {

        // 1) JSON blob: onepaqucpro_variations={"attribute_pa_brand":"dell"}
        if (!empty($_GET['onepaqucpro_variations'])) {
            $json_raw = wp_unslash($_GET['onepaqucpro_variations']);
            $decoded  = json_decode($json_raw, true);
            if (!is_array($decoded)) {
                // handle URL-encoded JSON
                $decoded = json_decode(urldecode($json_raw), true);
            }
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    $variation[onepaqucpro_normalize_attr_key($k)] = onepaqucpro_normalize_attr_value($v);
                }
            }
        }

        // 2) Param form: onepaqucpro_attribute_pa_color=blue
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'onepaqucpro_attribute_') === 0) {
                $attr_key = substr($key, strlen('onepaqucpro_attribute_'));
                $variation[onepaqucpro_normalize_attr_key($attr_key)] = onepaqucpro_normalize_attr_value($value);
            }
        }

        if ($variation_id <= 0) {
            wc_add_notice(__('Please select product options before adding to cart.', 'woocommerce'), 'error');
            return;
        }

        $variation_product = wc_get_product($variation_id);
        if (!$variation_product || $variation_product->get_parent_id() !== $product_id) {
            wc_add_notice(__('Invalid variation selected.', 'woocommerce'), 'error');
            return;
        }

        // Ensure all attributes required by the variation are present
        $required = $variation_product->get_variation_attributes(); // e.g. ['attribute_pa_color'=>'blue']
        foreach ($required as $req_key => $req_val) {
            if (empty($variation[$req_key])) {
                $variation[$req_key] = $req_val;
            }
        }
    }

    // Grouped products -> send to product page
    if ($product->is_type('grouped')) {
        wc_add_notice(__('Please select products from the group to add to cart.', 'woocommerce'), 'notice');
        wp_safe_redirect($product->get_permalink());
        exit;
    }

    // Stock check on actual purchasable
    $stock_product = ($variation_id > 0) ? wc_get_product($variation_id) : $product;
    if (!$stock_product || !$stock_product->has_enough_stock($quantity)) {
        wc_add_notice(
            sprintf(__('Sorry, we do not have enough "%s" in stock.', 'woocommerce'), $stock_product ? $stock_product->get_name() : $product->get_name()),
            'error'
        );
        return;
    }

    try {
        $added = false;

        if ($variation_id > 0) {
            $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
        } else {
            $added = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        }

        if ($added) {
            wc_add_to_cart_message(array($product_id => $quantity), true);

            // Always redirect to a clean /checkout/ (strip all onepaqucpro_* and attribute params)
            $redirect_url = onepaqucpro_get_clean_checkout_url();
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            wc_add_notice(__('Unable to add product to cart.', 'woocommerce'), 'error');
        }
    } catch (Exception $e) {
        wc_add_notice($e->getMessage(), 'error');
    }
}

/**
 * Normalize attribute key to Woo format (must start with 'attribute_').
 */
function onepaqucpro_normalize_attr_key($key)
{
    $key = wc_clean(wp_unslash($key));
    $key = strtolower($key);
    if (strpos($key, 'attribute_') !== 0) {
        $key = 'attribute_' . $key;
    }
    return $key;
}

/**
 * Normalize attribute value to the slug Woo expects (e.g. "Deep Blue" -> "deep-blue").
 */
function onepaqucpro_normalize_attr_value($value)
{
    return sanitize_title(wc_clean(wp_unslash($value)));
}

/**
 * Build a clean checkout URL without any onepaqucpro_* params, preventing re-add on refresh.
 */
function onepaqucpro_get_clean_checkout_url()
{
    // Base checkout URL
    $checkout_url = get_option('rmenupro_wc_checkout_method', 'direct_checkout') === "direct_checkout" ? wc_get_checkout_url() : wc_get_cart_url();

    $remove = array(
        'onepaqucpro_add-to-cart',
        'onepaqucpro_quantity',
        'onepaqucpro_variation_id',
        'onepaqucpro_variations',
    );

    if (!empty($_GET)) {
        foreach (array_keys($_GET) as $k) {
            if (strpos($k, 'onepaqucpro_attribute_') === 0) {
                $remove[] = $k;
            }
        }
    }

    return remove_query_arg(array_unique($remove), $checkout_url);
}

/**
 * Get cart redirect URL
 * 
 * @return string Redirect URL
 */
function onepaqucpro_get_cart_redirect_url()
{
    // Get the current URL without query parameters
    $current_url = home_url(add_query_arg(array(), wp_unslash($_SERVER['REQUEST_URI'])));

    // Remove all onepaqucpro parameters
    $redirect_url = remove_query_arg(
        array(
            'onepaqucpro_add-to-cart',
            'onepaqucpro_quantity',
            'onepaqucpro_variation_id'
        ),
        $current_url
    );

    // Remove attribute parameters
    $parsed_url = parse_url($current_url);
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
        foreach ($query_params as $key => $value) {
            if (strpos($key, 'onepaqucpro_attribute_') === 0) {
                $redirect_url = remove_query_arg($key, $redirect_url);
            }
        }
    }

    // Stay on the same page (cart, checkout, or wherever they were)
    return $redirect_url;
}

/**
 * Optional: Add custom cart item data
 * Use this filter to add custom data to cart items
 */
add_filter('woocommerce_add_cart_item_data', 'onepaqucpro_add_custom_cart_item_data', 10, 3);

function onepaqucpro_add_custom_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    // Check if this was added via our URL handler
    if (isset($_GET['onepaqucpro_add-to-cart'])) {
        // Add custom data here if needed
        // Example: $cart_item_data['custom_field'] = 'custom_value';

        // Add a unique identifier to prevent duplicate cart items from being merged
        // Remove this if you want products to merge in cart
        // $cart_item_data['unique_key'] = md5(microtime() . rand());
    }

    return $cart_item_data;
}

/**
 * Optional: Validate cart item before adding
 */
add_filter('woocommerce_add_to_cart_validation', 'onepaqucpro_validate_cart_item', 10, 3);

function onepaqucpro_validate_cart_item($passed, $product_id, $quantity)
{
    // Add custom validation rules here
    // Example: Check if user is logged in for certain products

    return $passed;
}


// Allow checkout page access even when the cart is empty
add_action('template_redirect', function () {
    if (is_checkout() && !is_wc_endpoint_url()) {
        remove_action('template_redirect', 'wc_redirect_empty_cart_to_cart');
    }
}, 1);

// Let users access checkout even if the cart is empty
add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false');
