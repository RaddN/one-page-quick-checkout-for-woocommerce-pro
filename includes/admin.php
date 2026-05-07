<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Admin Menu
add_action('admin_menu', 'onepaqucpro_cart_menu');
add_action('admin_head', 'onepaqucpro_cart_recovery_menu_badge_styles');


function onepaqucpro_cart_menu()
{

    add_menu_page(
        'Onpage Checkout',
        'Onpage Checkout',
        'manage_options',
        'onepaqucpro_cart',
        'onepaqucpro_cart_dashboard',
        'dashicons-cart', // Shopping cart icon
        '55.50'
    );
    add_submenu_page(
        'onepaqucpro_cart',
        'Cart Recovery',
        'Cart Recovery <span class="onepaqucpro-menu-new-badge">NEW!</span>',
        'manage_options',
        'onepaqucpro_cart_recovery',
        'onepaqucpro_cart_recovery_page'
    );

    // Hidden editor page (opened from "Add New Email" / "Edit" links).
    add_submenu_page(
        'onepaqucpro_cart',
        __('Edit Recovery Email', 'one-page-quick-checkout-for-woocommerce-pro'),
        '',
        'manage_options',
        'onepaqucpro_cart_recovery_template',
        'onepaqucpro_cart_recovery_template_page'
    );
    add_submenu_page(
        'onepaqucpro_cart',
        'Documentation',
        'Documentation',
        'manage_options',
        'onepaqucpro_cart_documentation',
        'onepaqucpro_cart_documentation'
    );
    if (get_option('onepaqucpro_validity_days') !== "0") {
        // add_submenu_page('bd-affiliate-marketing', 'Manage Posts', 'Manage Posts', 'manage_options', 'bd-manage-posts', 'onepaqucpro_marketing_manage_posts');
        // add_submenu_page('bd-affiliate-marketing', 'Send Notification', 'Send Notification', 'manage_options', 'bd-send-notification', 'onepaqucpro_marketing_send_notification');
    }
}

function onepaqucpro_cart_recovery_menu_badge_styles()
{
?>
    <style>
        #adminmenu .onepaqucpro-menu-new-badge {
            display: inline-block;
            margin-left: 5px;
            color: #f59e0b;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
            text-transform: uppercase;
            vertical-align: super;
        }

        .onepaqucpro-hidden-menu-item,
        #adminmenu a[href="admin.php?page=onepaqucpro_cart_recovery_template"] {
            display: none !important;
        }
    </style>
    <?php
}

// Display the form for Side Cart and PopUp settings
function onepaqucpro_cart_text_change_form($textvariable)
{
    $onepaquc_helper = new onepaqucpro_helper();
    $floating_cart_text_fields = array('your_cart', 'txt_subtotal', 'txt_checkout');
    $textvariable = array_diff_key($textvariable, array_flip($floating_cart_text_fields));

    echo '<div class="plugincy_card">';
    $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<svg fill="#fff" width="18" height="18" viewBox="0 0 0.27 0.27" xmlns="http://www.w3.org/2000/svg"><path d="M.27.022v.045a.022.022 0 0 1-.045 0V.045H.158v.18H.18a.022.022 0 0 1 0 .045H.09a.022.022 0 0 1 0-.045h.022v-.18H.045v.022a.022.022 0 1 1-.045 0V.022A.02.02 0 0 1 .022 0h.225a.02.02 0 0 1 .022.022"/></svg>', esc_html__('Cart Text Changes', 'one-page-quick-checkout-for-woocommerce'));

    echo '<div class="plugincy_grid" style="row-gap:10px">';

    foreach (array_chunk($textvariable, 4, true) as $column) {
        foreach ($column as $name => $label) {
            $value = esc_attr(get_option($name, ''));
    ?>
            <label>
                <?php $onepaquc_helper->sec_head('p', '', '', esc_html($label), 'You can find "' . esc_html($label) . '" in the checkout form or drawer' . ($name === "txt-complete_your_purchase" ? " on single product pages." : ".")); ?>
                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
            </label>
    <?php
        }
    }

    echo '</div> </div>';
}

function onepaqucpro_render_floating_cart_pro_switch($name, $label, $default = '1')
{
    $premium_enabled = function_exists('onepaqucpro_can_use_floating_cart_premium_settings') && onepaqucpro_can_use_floating_cart_premium_settings();
    $field_name = $premium_enabled ? $name : 'pro_' . $name;
    $value = $premium_enabled ? get_option($name, $default) : '0';
    ?>
    <div class="rmenupro-settings-field plugincy_row items_center onepaqucpro-floating-control <?php echo !$premium_enabled ? 'pro-only' : ''; ?>" data-floating-control-wrap="<?php echo esc_attr($name); ?>">
        <label class="rmenupro-settings-label"><?php echo esc_html($label); ?></label>
        <div class="rmenupro-settings-control">
            <label class="switch">
                <input type="checkbox" name="<?php echo esc_attr($field_name); ?>" value="1" data-floating-cart-control="<?php echo esc_attr($name); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?> <?php checked('1', $value, true); ?> />
                <span class="slider round"></span>
            </label>
            <span class="<?php echo !$premium_enabled ? 'dashicons dashicons-lock plugincy_lock-icon' : ''; ?>"></span>
        </div>
    </div>
    <?php
}

function onepaqucpro_render_floating_cart_pro_text_field($name, $label, $placeholder = '')
{
    $premium_enabled = function_exists('onepaqucpro_can_use_floating_cart_premium_settings') && onepaqucpro_can_use_floating_cart_premium_settings();
    $field_name = $premium_enabled ? $name : 'pro_' . $name;
    $value = $premium_enabled ? get_option($name, '') : '';
    ?>
    <label class="rmenupro-settings-field onepaqucpro-floating-control <?php echo !$premium_enabled ? 'pro-only' : ''; ?>" data-floating-control-wrap="<?php echo esc_attr($name); ?>">
        <span class="rmenupro-settings-label"><?php echo esc_html($label); ?></span>
        <span class="rmenupro-settings-control">
            <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" data-floating-cart-control="<?php echo esc_attr($name); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?> />
            <span class="<?php echo !$premium_enabled ? 'dashicons dashicons-lock plugincy_lock-icon' : ''; ?>"></span>
        </span>
    </label>
    <?php
}

function onepaqucpro_get_floating_cart_admin_preview_text($name, $defaults, $fallback = '')
{
    $default = isset($defaults[$name]) ? $defaults[$name] : $fallback;
    $value = get_option($name, $default);

    return trim((string) $value) !== '' ? $value : $default;
}

function onepaqucpro_get_floating_cart_admin_meta_preview_value($key)
{
    $key = strtolower((string) $key);

    if (function_exists('onepaqucpro_is_floating_cart_variations_meta_key') && onepaqucpro_is_floating_cart_variations_meta_key($key)) {
        return 'Black, S';
    }

    if (strpos($key, 'location') !== false) {
        return 'New-York';
    }

    if (strpos($key, 'color') !== false || strpos($key, 'colour') !== false) {
        return 'Black';
    }

    if (strpos($key, 'size') !== false) {
        return 'S';
    }

    if (strpos($key, 'brand') !== false) {
        return 'Plugincy';
    }

    if (strpos($key, 'store') !== false || strpos($key, 'warehouse') !== false) {
        return 'Main Store';
    }

    return 'Sample value';
}

function onepaqucpro_get_floating_cart_admin_meta_options()
{
    $options = array();
    $variation_meta_key = function_exists('onepaqucpro_get_floating_cart_variations_meta_key') ? onepaqucpro_get_floating_cart_variations_meta_key() : 'onepaqucpro_variations';

    $options[$variation_meta_key] = __('Variations', 'one-page-quick-checkout-for-woocommerce-pro');

    if (function_exists('onepaqucpro_maybe_load_floating_cart_context')) {
        onepaqucpro_maybe_load_floating_cart_context();
    }

    if (function_exists('WC') && WC() && WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            foreach (onepaqucpro_get_floating_cart_cart_item_meta_rows($cart_item) as $row) {
                if (empty($row['id']) || empty($row['label'])) {
                    continue;
                }

                if (onepaqucpro_is_hidden_floating_cart_admin_meta_key($row['id'])) {
                    continue;
                }

                $label = (string) $row['label'];
                if (isset($row['source']) && $row['source'] === 'raw') {
                    $label .= ' (' . $row['id'] . ')';
                }

                $options[$row['id']] = $label;
            }
        }
    }

    natcasesort($options);

    return $options;
}

function onepaqucpro_get_hidden_floating_cart_admin_meta_keys()
{
    return apply_filters('onepaqucpro_hidden_floating_cart_admin_meta_keys', onepaqucpro_get_hidden_floating_cart_meta_keys());
}

function onepaqucpro_is_hidden_floating_cart_admin_meta_key($meta_key)
{
    return in_array(
        onepaqucpro_normalize_floating_cart_meta_identifier($meta_key),
        array_map('onepaqucpro_normalize_floating_cart_meta_identifier', onepaqucpro_get_hidden_floating_cart_admin_meta_keys()),
        true
    );
}

function onepaqucpro_render_floating_cart_meta_picker($name, $label, $multiple = true)
{
    $premium_enabled = function_exists('onepaqucpro_can_use_floating_cart_premium_settings') && onepaqucpro_can_use_floating_cart_premium_settings();
    $field_name = $premium_enabled ? $name : 'pro_' . $name;
    $value = $premium_enabled ? get_option($name, '') : '';
    $selected_values = array_filter(array_map('trim', explode(',', (string) $value)));
    $selected_values = array_values(array_filter($selected_values, function ($selected_value) {
        return !onepaqucpro_is_hidden_floating_cart_admin_meta_key($selected_value);
    }));
    $value = implode(',', $selected_values);
    $options = onepaqucpro_get_floating_cart_admin_meta_options();
    ?>
    <div class="rmenupro-settings-field plugincy_row items_center onepaqucpro-floating-control" data-floating-control-wrap="<?php echo esc_attr($name); ?>">
        <label class="rmenupro-settings-label"><?php echo esc_html($label); ?></label>
        <div class="rmenupro-settings-control onepaqucpro-meta-picker" data-meta-picker="<?php echo esc_attr($name); ?>" data-meta-picker-multiple="<?php echo $multiple ? '1' : '0'; ?>">
            <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>" data-floating-cart-control="<?php echo esc_attr($name); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?> />
            <select class="<?php echo $multiple ? 'onepaqucpro-meta-picker-select2' : ''; ?>" <?php echo $multiple ? 'multiple data-use-select2="1"' : ''; ?> <?php echo !$premium_enabled ? 'disabled' : ''; ?>>
                <?php foreach ($options as $option_value => $option_label) : ?>
                    <option value="<?php echo esc_attr($option_value); ?>" <?php selected(in_array($option_value, $selected_values, true), true); ?>><?php echo esc_html($option_label); ?></option>
                <?php endforeach; ?>
                <?php foreach ($selected_values as $selected_value) : ?>
                    <?php if (!isset($options[$selected_value])) : ?>
                        <option value="<?php echo esc_attr($selected_value); ?>" selected><?php echo esc_html($selected_value); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="onepaqucpro-meta-picker__custom">
                <input type="text" placeholder="<?php esc_attr_e('Add custom meta key', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?> />
                <button type="button" class="button" <?php echo !$premium_enabled ? 'disabled' : ''; ?>><?php esc_html_e('Add', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
            </div>
        </div>
    </div>
    <?php
}

function onepaqucpro_render_floating_cart_meta_rule_builder($name, $label)
{
    $premium_enabled = function_exists('onepaqucpro_can_use_floating_cart_premium_settings') && onepaqucpro_can_use_floating_cart_premium_settings();
    $field_name = $premium_enabled ? $name : 'pro_' . $name;
    $saved_value = $premium_enabled ? get_option($name, '') : '';
    $options = onepaqucpro_get_floating_cart_admin_meta_options();
    $rules = onepaqucpro_decode_floating_cart_meta_rules($saved_value, $options);
    $rules = onepaqucpro_ensure_floating_cart_variations_meta_rule($rules);
    $value = onepaqucpro_encode_floating_cart_meta_rules($rules);
    $options_payload = array();

    foreach ($options as $option_value => $option_label) {
        $options_payload[] = array(
            'value' => (string) $option_value,
            'label' => wp_strip_all_tags((string) $option_label),
        );
    }
    ?>
    <div class="rmenupro-settings-field plugincy_row items_center onepaqucpro-floating-control" data-floating-control-wrap="<?php echo esc_attr($name); ?>">
        <label class="rmenupro-settings-label"><?php echo esc_html($label); ?></label>
        <div class="rmenupro-settings-control onepaqucpro-meta-builder" data-meta-builder="<?php echo esc_attr($name); ?>" data-meta-builder-options="<?php echo esc_attr(wp_json_encode($options_payload)); ?>">
            <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>" data-floating-cart-control="<?php echo esc_attr($name); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?> />
            <div class="onepaqucpro-meta-builder__head" aria-hidden="true">
                <span></span>
                <span><?php esc_html_e('Meta', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                <span><?php esc_html_e('Mode', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                <span><?php esc_html_e('Title', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                <span></span>
            </div>
            <div class="onepaqucpro-meta-builder__rows" data-meta-builder-rows>
                <?php foreach ($rules as $rule) : ?>
                    <?php
                    $rule_key = isset($rule['key']) ? (string) $rule['key'] : '';
                    $rule_title = isset($rule['title']) ? (string) $rule['title'] : '';
                    $rule_mode = isset($rule['mode']) ? onepaqucpro_sanitize_floating_cart_meta_mode($rule['mode']) : 'separate';
                    $rule_is_variations = function_exists('onepaqucpro_is_floating_cart_variations_meta_key') && onepaqucpro_is_floating_cart_variations_meta_key($rule_key);
                    $rule_title_hidden = $rule_is_variations && $rule_mode !== 'combine';
                    ?>
                    <div class="onepaqucpro-meta-builder__row" data-meta-builder-row draggable="true">
                        <button type="button" class="onepaqucpro-meta-builder__drag" data-meta-builder-drag aria-label="<?php esc_attr_e('Reorder meta data', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?>>
                            <span class="dashicons dashicons-menu"></span>
                        </button>
                        <select data-meta-builder-key <?php echo !$premium_enabled ? 'disabled' : ''; ?>>
                            <?php foreach ($options as $option_value => $option_label) : ?>
                                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($rule_key, $option_value); ?>><?php echo esc_html($option_label); ?></option>
                            <?php endforeach; ?>
                            <?php if ($rule_key !== '' && !isset($options[$rule_key])) : ?>
                                <option value="<?php echo esc_attr($rule_key); ?>" selected><?php echo esc_html($rule_key); ?></option>
                            <?php endif; ?>
                        </select>
                        <select data-meta-builder-mode <?php echo !$premium_enabled || !$rule_is_variations ? 'disabled' : ''; ?>>
                            <option value="separate" <?php selected($rule_mode, 'separate'); ?>><?php esc_html_e('Separate', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="combine" <?php selected($rule_mode, 'combine'); ?>><?php esc_html_e('Combine', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                        <input type="text" value="<?php echo esc_attr($rule_title); ?>" placeholder="<?php esc_attr_e('Display title', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" data-meta-builder-title <?php echo !$premium_enabled || $rule_title_hidden ? 'disabled' : ''; ?> <?php echo $rule_title_hidden ? 'hidden' : ''; ?> />
                        <button type="button" class="onepaqucpro-meta-builder__remove" data-meta-builder-remove aria-label="<?php esc_attr_e('Remove meta data', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" <?php echo !$premium_enabled ? 'disabled' : ''; ?>>
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button onepaqucpro-meta-builder__add" data-meta-builder-add <?php echo !$premium_enabled ? 'disabled' : ''; ?>><?php esc_html_e('Add meta data', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
        </div>
    </div>
    <?php
}

// Dashboard page
function onepaqucpro_cart_dashboard()
{
    global $onepaqucpro_checkoutformfields;

    $onepaquc_helper = new onepaqucpro_helper();
    ?>

    <div class="welcome-banner">
        <div class="welcome-title">Welcome to One Page Quick Checkout for WooCommerce Pro <span class="version-tag">v1.1.9.35</span></div>
        <p style="max-width: 70%; margin:0 auto;">Thank you for installing One Page Quick Checkout for WooCommerce! Streamline your WooCommerce checkout process and boost your conversion rates with our easy-to-configure solution.</p>
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg fill="#fff" width="30" height="30" viewBox="-0.282 -0.132 0.9 0.9" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin" class="jam jam-thunder-f">
                        <path d="M.214.186h.068a.057.057 0 0 1 .051.078L.184.611A.039.039 0 0 1 .109.596v-.26L.054.334A.06.06 0 0 1 0 .279V.057A.06.06 0 0 1 .057 0h.101a.06.06 0 0 1 .057.057z" />
                    </svg>
                </div>
                <h3>Fast Setup</h3>
                <p>Configure your checkout in minutes with our intuitive options.</p>
            </div>
            <div class="feature-item" style="background: #ebfcf1;">
                <div class="feature-icon" style="background: #22c55e;">
                    <svg width="30" height="30" viewBox="0 0 0.75 0.75" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#fff" d="M.563 0a.075.075 0 0 1 .075.075v.6A.075.075 0 0 1 .563.75H.188A.075.075 0 0 1 .113.675v-.6A.075.075 0 0 1 .188 0zm.022.577h-.42v.098a.02.02 0 0 0 .022.022h.375A.02.02 0 0 0 .584.675zM.375.6a.037.037 0 1 1 0 .075.037.037 0 0 1 0-.075M.563.053H.188a.02.02 0 0 0-.022.022v.45h.42v-.45A.02.02 0 0 0 .564.053" />
                    </svg>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Responsive design works perfectly on all devices.</p>
            </div>
            <div class="feature-item" style="background: #f8f2ff;">
                <div class="feature-icon" style="background: #a855f7;">
                    <svg width="30" height="30" viewBox="0 0 0.9 0.9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M.075.319h.75m-.6.3H.3m.094 0h.15" stroke="#fff" stroke-width=".056" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M.241.131h.417c.134 0 .167.033.167.165v.308c0 .132-.033.165-.167.165H.241C.108.769.074.736.074.605V.297c0-.132.033-.165.167-.165" stroke="#fff" stroke-width=".056" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3>Payment Gateways</h3>
                <p>Supports all major payment processors.</p>
            </div>
        </div>

        <div class="button-row">
            <a target="_blank" href="https://demo.plugincy.com/one-page-quick-checkout-for-woocommerce/" class="button" style="background: #ed8936;color: white;"><span class="dashicons dashicons-visibility" style=" margin-right: 5px; "></span> View Demo</a>
            <a target="_blank" href="https://plugincy.com/documentations/one-page-quick-checkout-for-woocommerce/" class="button"><span class="dashicons dashicons-book" style=" margin-right: 5px; "></span> View Documentation</a>
            <a href="https://plugincy.com/support" target="_blank" class="button button-secondary"><span class="dashicons dashicons-sos" style=" margin-right: 5px; "></span> Get Support</a>
        </div>
    </div>

    <h1 style="padding-top: 3rem;">Dashboard</h1>
    <?php
    // Check if the action parameter is set to reset_success
    if (isset($_GET['action']) && $_GET['action'] === 'reset_success') {
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Reset successful!', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
        </div>
    <?php
    }
    ?>
    <?php
    // if (get_option('onepaqucpro_validity_days') === "0" || !get_option('onepaqucpro_api_key')) {
    //     echo "<p style='color:red;'>To use the plugin please active your API key first.</p>";
    // } else { 
    ?>
    <div class="tab-container">
        <div class="tabs">
            <div class="tab active" data-tab="4">
                <span class="dashicons dashicons-cart"></span>
                Direct Checkout
            </div>
            <div class="tab" data-tab="2">
                <span class="dashicons dashicons-admin-page"></span>
                One Page Checkout
            </div>
            <div class="tab" data-tab="9">
                <span class="dashicons dashicons-archive"></span>
                Floating Cart
            </div>

            <div class="tab" data-tab="7">
                <span class="dashicons dashicons-visibility"></span>
                Quick View
            </div>
            <div class="tab" data-tab="5">
                <span class="dashicons dashicons-star-filled"></span>
                Features
            </div>
            <div class="tab" data-tab="8">
                <span class="dashicons dashicons-plus-alt"></span>
                Add To Cart
            </div>
            <div class="tab" data-tab="0">
                <span class="dashicons dashicons-forms"></span>
                Checkout Form
            </div>
            <div class="tab" data-tab="3">
                <span class="dashicons dashicons-edit"></span>
                Text Manage
            </div>
            <div class="tab" data-tab="6">
                <span class="dashicons dashicons-admin-settings"></span>
                Advanced Settings
            </div>
            <div class="tab" data-tab="100">
                <span class="dashicons dashicons-admin-network"></span>
                Plugin License
            </div>
        </div>
        <script>
            function toggleDisabledClass(isDisabled, allinputFields) {
                if (!Array.isArray(allinputFields)) {
                    if (isDisabled) {
                        allinputFields.classList.add('disabled');
                        allinputFields.readOnly = true;
                    } else {
                        allinputFields.classList.remove('disabled');
                        allinputFields.readOnly = false;
                    }
                } else {
                    allinputFields.forEach(field => {
                        if (isDisabled) {
                            field.classList.add('disabled');
                            field.readOnly = true;
                        } else {
                            field.classList.remove('disabled');
                            field.readOnly = false;
                        }
                    });
                }
            }

            function isColorDark(color) {
                if (!color) return false;

                // Remove # if present and convert to lowercase
                const hex = color.replace('#', '').toLowerCase();

                // Handle 3-digit hex
                let fullHex = hex;
                if (hex.length === 3) {
                    fullHex = hex.split('').map(char => char + char).join('');
                }

                // Validate hex format
                if (fullHex.length !== 6 || !/^[0-9a-f]{6}$/.test(fullHex)) {
                    return false;
                }

                // Parse RGB values
                const r = parseInt(fullHex.slice(0, 2), 16);
                const g = parseInt(fullHex.slice(2, 4), 16);
                const b = parseInt(fullHex.slice(4, 6), 16);

                // Calculate relative luminance
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

                // Color is dark if luminance is less than 0.5
                return luminance < 0.5;
            }

            function checkColors(checkoutColor, checkoutTextColor, is_warn_show = true) {
                const bgColor = checkoutColor.value;
                const textColor = checkoutTextColor.value;

                if (!is_warn_show) {
                    return;
                }

                if (isColorDark(bgColor) && isColorDark(textColor)) {
                    showDirectCheckoutWarning(
                        checkoutColor.closest('.rmenupro-settings-row'),
                        'Warning: Both background and text colors are dark. This may affect readability.'
                    );
                } else if (!isColorDark(bgColor) && !isColorDark(textColor)) {
                    showDirectCheckoutWarning(
                        checkoutTextColor.closest('.rmenupro-settings-row'),
                        'Warning: Both background and text colors are light. This may affect readability.'
                    );
                } else {
                    removeDirectCheckoutWarning(checkoutColor.closest('.rmenupro-settings-row'));
                    removeDirectCheckoutWarning(checkoutTextColor.closest('.rmenupro-settings-row'));
                }
            }
        </script>
        <div class="tab-content" id="tab-0">
            <?php
            require_once plugin_dir_path(__FILE__) . 'checkout_form_editor.php';
            ?>
        </div>
        <div class="tab-content" id="tab-100">
            <?php
            global $onepaqucpro_License_Manager;
            $onepaqucpro_License_Manager->render_license_form();
            ?>
        </div>
        <form method="post" action="options.php">
            <!-- Add nonce field for security -->
            <?php wp_nonce_field('onepaqucpro_cart_settings'); ?>
            <?php settings_fields('onepaqucpro_cart_settings'); ?>
            <?php if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
                $default_config = array(
                    'coupon-section' => array(
                        'visible' => true,
                        'title' => 'Have a coupon?',
                        'description' => 'If you have a coupon code, please apply it below.',
                        'placeholder' => 'Coupon code',
                        'button' => 'Apply Coupon'
                    ),
                    'billing-title' => array(
                        'visible' => true,
                        'text' => 'Billing details'
                    ),
                    'first-name' => array(
                        'visible' => true,
                        'label' => 'First name',
                        'placeholder' => 'Enter your first name',
                        'required' => true
                    ),
                    'last-name' => array(
                        'visible' => true,
                        'label' => 'Last name',
                        'placeholder' => 'Enter your last name',
                        'required' => true
                    ),
                    'email' => array(
                        'visible' => true,
                        'label' => 'Email address',
                        'placeholder' => 'Enter your email address',
                        'required' => true
                    ),
                    'phone' => array(
                        'visible' => true,
                        'label' => 'Phone number',
                        'placeholder' => 'Enter your phone number',
                        'required' => false
                    ),
                    'country' => array(
                        'visible' => true,
                        'label' => 'Country / Region',
                        'required' => true
                    ),
                    'address' => array(
                        'visible' => true,
                        'label' => 'Street address',
                        'placeholder' => 'House number and street name',
                        'required' => true
                    ),
                    'address2' => array(
                        'visible' => true,
                        'placeholder' => 'Apartment, suite, unit, etc. (optional)'
                    ),
                    'city' => array(
                        'visible' => true,
                        'label' => 'Town / City',
                        'placeholder' => 'Enter your city',
                        'required' => true
                    ),
                    'state' => array(
                        'visible' => true,
                        'label' => 'State / District',
                        'placeholder' => 'Enter your state',
                        'required' => true
                    ),
                    'postcode' => array(
                        'visible' => true,
                        'label' => 'Postcode / ZIP',
                        'placeholder' => 'Enter your postcode',
                        'required' => true
                    ),
                    'company' => array(
                        'visible' => true,
                        'label' => 'Company name',
                        'placeholder' => 'Enter your company name',
                        'required' => false
                    ),
                    'ship-to-different' => array(
                        'visible' => true,
                        'label' => 'Ship to a different address?'
                    ),
                    'shipping-first-name' => array(
                        'visible' => true,
                        'label' => 'First name',
                        'placeholder' => 'Enter shipping first name',
                        'required' => true
                    ),
                    'shipping-last-name' => array(
                        'visible' => true,
                        'label' => 'Last name',
                        'placeholder' => 'Enter your shipping last name',
                        'required' => true
                    ),
                    'shipping-country' => array(
                        'visible' => true,
                        'label' => 'Country / Region',
                        'required' => true
                    ),
                    'shipping-address' => array(
                        'visible' => true,
                        'label' => 'Street address',
                        'placeholder' => 'House number and street name',
                        'required' => true
                    ),
                    'shipping-address2' => array(
                        'visible' => true,
                        'placeholder' => 'Apartment, suite, unit, etc. (optional)'
                    ),
                    'shipping-city' => array(
                        'visible' => true,
                        'label' => 'Town / City',
                        'placeholder' => 'Enter shipping city',
                        'required' => true
                    ),
                    'shipping-state' => array(
                        'visible' => true,
                        'label' => 'State / District',
                        'placeholder' => 'Enter shipping state',
                        'required' => true
                    ),
                    'shipping-postcode' => array(
                        'visible' => true,
                        'label' => 'Postcode / ZIP',
                        'placeholder' => 'Enter shipping postcode',
                        'required' => true
                    ),
                    'shipping-company' => array(
                        'visible' => true,
                        'label' => 'Company name',
                        'placeholder' => 'Enter shipping company name',
                        'required' => false
                    ),
                    'additional-title' => array(
                        'visible' => true,
                        'text' => 'Additional information'
                    ),
                    'order-notes' => array(
                        'visible' => true,
                        'label' => 'Order notes',
                        'placeholder' => 'Notes about your order, e.g. special notes for delivery.',
                        'required' => false
                    ),
                    'order-review-title' => array(
                        'visible' => true,
                        'text' => 'Your order'
                    ),
                    'order-summary' => array(
                        'visible' => true
                    ),
                    'product-header' => array(
                        'visible' => true,
                        'text' => 'Product'
                    ),
                    'subtotal-header' => array(
                        'visible' => true,
                        'text' => 'Subtotal'
                    ),
                    'order-item' => array(
                        'visible' => true,
                        'text' => 'Sample Product × 1'
                    ),
                    'order-item-price' => array(
                        'visible' => true,
                        'text' => '$29.99'
                    ),
                    'subtotal2' => array(
                        'visible' => true
                    ),
                    'subtotal-price' => array(
                        'visible' => true,
                        'text' => '$29.99'
                    ),
                    'shipping' => array(
                        'visible' => true,
                        'text' => 'Shipping'
                    ),
                    'shipping-price' => array(
                        'visible' => true,
                        'text' => '$5.00'
                    ),
                    'total-header' => array(
                        'visible' => true,
                        'text' => 'Total'
                    ),
                    'total-price' => array(
                        'visible' => true,
                        'text' => '$34.99'
                    ),
                    'payment-methods' => array(
                        'visible' => true
                    ),
                    'privacy-policy' => array(
                        'visible' => true,
                        'text' => 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.'
                    ),
                    'place-order' => array(
                        'visible' => true,
                        'text' => 'Place order'
                    )
                );
            ?>
                <input type="hidden" name="checkout_form_setup" id="checkout_setup" value="<?php echo esc_attr(get_option("checkout_form_setup", wp_json_encode($default_config))); ?>">
            <?php } ?>
            <div class="tab-content" id="tab-2">
                <!-- Tooltip CSS -->
                <style>
                    .tooltip {
                        position: relative;
                        display: inline-block;
                        cursor: help;
                        margin-left: 5px;
                    }

                    .tooltip .question-mark {
                        display: inline-block;
                        width: 16px;
                        height: 16px;
                        line-height: 16px;
                        text-align: center;
                        background: #f0f0f0;
                        color: #555;
                        border-radius: 50%;
                        font-size: 12px;
                        font-weight: bold;
                    }

                    .tooltip .tooltip-text {
                        visibility: hidden;
                        width: 250px;
                        background-color: #555;
                        color: #fff;
                        text-align: left;
                        border-radius: 4px;
                        padding: 8px;
                        position: absolute;
                        z-index: 1;
                        bottom: 125%;
                        left: 50%;
                        margin-left: -125px;
                        opacity: 0;
                        transition: opacity 0.3s;
                        font-weight: normal;
                        font-size: 12px;
                        line-height: 1.4;
                    }

                    .tooltip:hover .tooltip-text {
                        visibility: visible;
                        opacity: 1;
                    }
                </style>

                <div class="plugincy_row">
                    <div class="plugincy_col-5 plugincy_card">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<svg fill="#fff" height="16" width="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10.24 10.24" xml:space="preserve">
                                    <path d="M8.424 2.56H7.57v.426a.855.855 0 0 1-1.708 0V2.56H4.156v.426a.855.855 0 0 1-1.708 0V2.56h-.85c0 4.266-.426 7.68-.426 7.68h7.68c-.002 0-.428-3.414-.428-7.68m-5.12.854c.236 0 .426-.19.426-.426v-.854c0-.708.572-1.28 1.28-1.28s1.28.572 1.28 1.28v.854a.425.425 0 1 0 .852 0v-.854C7.144.956 6.188 0 5.01 0S2.876.956 2.876 2.134v.854a.43.43 0 0 0 .428.426" />
                                </svg>', 'One Page Checkout in Single Product', 'Configure one-page checkout for individual product pages. Enable one-page checkout for specific products from the WooCommerce product edit screen.'); ?>
                        <table class="form-table plugincy_table">
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable One Page Checkout', 'Enable one-page checkout for single product page.'); ?><td>
                                    <?php
                                    $onepaquc_helper->switcher('onepaqucpro_checkout_enable');
                                    ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable for All Products', 'Enable one-page checkout for all products without individual selection.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_enable_all', 0); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Form Position', 'Set the priority of the one-page checkout form. The default value is 9, but not all values work with every theme. So, adjust it based on your theme.'); ?>
                                <td>
                                    <?php
                                    // Get the saved value or default to 9 if not set or empty
                                    $onepaqucpro_checkout_position = get_option("onepaqucpro_checkout_position", '');
                                    if ($onepaqucpro_checkout_position === '' || $onepaqucpro_checkout_position === false) {
                                        $onepaqucpro_checkout_position = 9;
                                    }
                                    ?>
                                    <input type="number" name="onepaqucpro_checkout_position" value="<?php echo esc_attr($onepaqucpro_checkout_position); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Empty Cart on Page Load', 'Clear existing cart items when the one-page checkout product page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_cart_empty', 0); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Add to Cart on Page Load', 'Automatically add the product to cart when the page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_cart_add', 1, "If you turn it off one page checkout will not work properly."); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Hide Add to Cart Button', 'Hide the regular Add to Cart button on one-page checkout product pages.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_hide_cart_button', 0); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Checkout Layout', 'Select the layout for the one-page checkout form.'); ?>
                                <td class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <select name="<?php echo !onepaqucpro_premium_feature() ? 'pro_checkout_layout' : 'onepaqucpro_checkout_layout'; ?>" <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?>>
                                        <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="<?php echo !onepaqucpro_premium_feature() ? 'pro_two_column' : 'two_column'; ?>" <?php selected(get_option('onepaqucpro_checkout_layout', 'two_column'), 'two_column'); ?>>Two Columns (Product & Checkout)</option>
                                        <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="<?php echo !onepaqucpro_premium_feature() ? 'pro_one_column' : 'one_column'; ?>" <?php selected(get_option('onepaqucpro_checkout_layout', 'two_column'), 'one_column'); ?>>One Column (Stacked)</option>
                                        <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="<?php echo !onepaqucpro_premium_feature() ? 'pro_product_first' : 'product_first'; ?>" <?php selected(get_option('onepaqucpro_checkout_layout', 'two_column'), 'product_first'); ?>>Product First, Then Checkout</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const enableCheckout = document.querySelector('div#tab-2 input[name="onepaqucpro_checkout_enable"]');

                            const allinputFields = Array.from(document.querySelectorAll('div#tab-2 table:nth-of-type(1) input, div#tab-2 table:nth-of-type(1) select')).filter(
                                el => !(el.name === "onepaqucpro_checkout_enable")
                            );
                            toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                            enableCheckout.addEventListener('change', function() {
                                toggleDisabledClass(!this.checked, allinputFields);
                            });
                        });
                    </script>
                    <div class="plugincy_col-5 plugincy_card">

                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-forms"></span>', 'Multi-product One Page Checkout', 'Configure settings for the multi-product one-page checkout shortcode. Use: [plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"]'); ?>

                        <table class="form-table plugincy_table">
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Empty Cart on Page Load', 'Clear existing cart items when a multi-product one-page checkout page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_widget_cart_empty'); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Add to Cart on Page Load', 'Automatically add the first product to cart when the page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onepaqucpro_checkout_widget_cart_add'); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-content" id="tab-3">
                <!-- <div class="d-flex space-between">
                    <h2>Checkout Form Text</h2> <button id="reset-defaults" class="button button-primary" style="background:red;">Reset Default</button>
                </div> -->
                <?php
                onepaqucpro_cart_text_change_form($onepaqucpro_checkoutformfields);

                ?>
            </div>
            <div class="tab-content" id="tab-9">
                <?php $floating_cart_visual_editor_enabled = function_exists('onepaqucpro_can_use_floating_cart_premium_settings') && onepaqucpro_can_use_floating_cart_premium_settings(); ?>
                <div id="onepaqucpro-floating-cart-editor" class="onepaqucpro-floating-editor <?php echo !$floating_cart_visual_editor_enabled ? 'is-pro-locked' : ''; ?>" data-floating-cart-visual-editor-enabled="<?php echo $floating_cart_visual_editor_enabled ? '1' : '0'; ?>">
                <div class="onepaqucpro-floating-editor-left">
                <div class="mb-4 onepaqucpro-floating-editor-intro">
                    <?php $onepaquc_helper->sec_head('h2', '', '<span class="dashicons dashicons-cart"></span>', 'Floating Cart Settings', ''); ?>
                    <p class="description">Configure the appearance and behavior of your floating cart. Enable the floating cart to provide users with quick access to their cart from any page on your site.</p>
                </div>

                <div class="plugincy_row mb-4">
                    <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', 'General settings for the floating cart.'); ?>

                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Sticky Cart', 'Enable or disable the sticky cart functionality.'); ?>
                                <td class="rmenupro-settings-control">
                                    <label class="switch">
                                        <input type="checkbox" name="rmenu_enable_sticky_cart" value="1" data-floating-cart-control="rmenu_enable_sticky_cart" <?php checked(1, get_option('rmenu_enable_sticky_cart', 0), true); ?> />
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Floating Cart Icon', 'Choose the icon shown on the floating cart launcher.'); ?>
                                <td class="rmenupro-settings-control">
                                    <?php $floating_cart_icon = get_option('rmenu_floating_cart_icon', 'cart'); ?>
                                    <select name="rmenu_floating_cart_icon" class="rmenu-select" data-floating-cart-control="rmenu_floating_cart_icon">
                                        <option value="cart" <?php selected($floating_cart_icon, 'cart'); ?>>Cart</option>
                                        <option value="shopping-bag" <?php selected($floating_cart_icon, 'shopping-bag'); ?>>Shopping Bag</option>
                                        <option value="basket" <?php selected($floating_cart_icon, 'basket'); ?>>Basket</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>" data-floating-control-wrap="rmenu_floating_cart_empty_icon">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Empty Cart Icon', 'Choose the icon shown in the empty floating cart drawer.'); ?>
                                <td class="rmenupro-settings-control">
                                    <?php
                                    $floating_empty_icon = function_exists('onepaqucpro_get_floating_cart_empty_icon') ? onepaqucpro_get_floating_cart_empty_icon() : 'cart';
                                    $floating_empty_icon_options = function_exists('onepaqucpro_get_floating_cart_empty_icon_options') ? onepaqucpro_get_floating_cart_empty_icon_options() : array('cart' => 'Cart');
                                    ?>
                                    <select name="<?php echo !onepaqucpro_premium_feature() ? 'pro_rmenu_floating_cart_empty_icon' : 'rmenu_floating_cart_empty_icon'; ?>" class="rmenu-select" data-floating-cart-control="rmenu_floating_cart_empty_icon" <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?>>
                                        <?php foreach ($floating_empty_icon_options as $empty_icon_key => $empty_icon_label) : ?>
                                            <option value="<?php echo esc_attr($empty_icon_key); ?>" <?php selected($floating_empty_icon, $empty_icon_key); ?>><?php echo esc_html($empty_icon_label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="<?php echo !onepaqucpro_premium_feature() ? 'dashicons dashicons-lock plugincy_lock-icon' : ''; ?>"></span>
                                </td>
                            </tr>

                            <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Hide If Cart Empty', 'Hide the floating cart button while the cart is empty.'); ?>
                                <td class="rmenupro-settings-control">
                                    <label class="switch">
                                        <input type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_rmenu_hide_empty_cart_button' : 'rmenu_hide_empty_cart_button'; ?>" value="1" data-floating-cart-control="rmenu_hide_empty_cart_button" <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> <?php checked(1, onepaqucpro_premium_feature() ? get_option('rmenu_hide_empty_cart_button', 0) : 0, true); ?> />
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="<?php echo !onepaqucpro_premium_feature() ? 'dashicons dashicons-lock plugincy_lock-icon' : ''; ?>"></span>
                                </td>
                            </tr>

                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Cart Checkout Behavior', 'Choose the behavior of the cart checkout process.'); ?>
                                <td class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $floating_cart_checkout_behavior = function_exists('onepaqucpro_get_floating_cart_checkout_behavior') ? onepaqucpro_get_floating_cart_checkout_behavior() : get_option('rmenu_cart_checkout_behavior', 'direct_checkout'); ?>
                                    <select name="rmenu_cart_checkout_behavior" class="rmenu-select" data-floating-cart-control="rmenu_cart_checkout_behavior">
                                        <option value="direct_checkout" <?php selected($floating_cart_checkout_behavior, 'direct_checkout'); ?>>Direct Checkout</option>
                                        <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="popup_checkout" <?php selected($floating_cart_checkout_behavior, 'popup_checkout'); ?>>Popup Checkout</option>
                                    </select>
                                    <span class="<?php echo !onepaqucpro_premium_feature() ? 'dashicons dashicons-lock plugincy_lock-icon' : ''; ?>"></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-move"></span>', 'Position Settings', 'Configure the position of the floating cart.'); ?>

                        <table class="form-table plugincy_table">

                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Top Position', 'Set the top position of the floating cart.'); ?>
                                <td class="rmenupro-settings-control">
                                    <input type="text" name="rmenu_cart_top_position" value="<?php echo esc_attr(get_option('rmenu_cart_top_position', '50%')); ?>" data-floating-cart-control="rmenu_cart_top_position" />
                                </td>
                            </tr>

                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Left Position', 'Set the left position of the floating cart.'); ?>
                                <td class="rmenupro-settings-control">
                                    <input type="text" name="rmenu_cart_left_position" value="<?php echo esc_attr(get_option('rmenu_cart_left_position', '100%')); ?>" data-floating-cart-control="rmenu_cart_left_position" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Floating Cart Colors & Button Shape', 'Configure the cart primary colors used by the launcher and drawer action buttons.'); ?>


                    <div class="rmenupro-settings-row rmenupro-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenupro-settings-field  plugincy_row items_center">
                                <label class="rmenupro-settings-label">Primary Color</label>
                                <div class="rmenupro-settings-control">
                                    <input type="color" name="rmenu_cart_bg_color" value="<?php echo esc_attr(get_option('rmenu_cart_bg_color', '#96588a')); ?>" class="rmenu-color-picker" data-floating-cart-control="rmenu_cart_bg_color" />
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-column">
                            <div class="rmenupro-settings-field  plugincy_row items_center">
                                <label class="rmenupro-settings-label">Primary Text Color</label>
                                <div class="rmenupro-settings-control">
                                    <input type="color" name="rmenu_cart_text_color" value="<?php echo esc_attr(get_option('rmenu_cart_text_color', '#ffffff')); ?>" class="rmenu-color-picker" data-floating-cart-control="rmenu_cart_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenupro-settings-row rmenupro-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenupro-settings-field  plugincy_row items_center">
                                <label class="rmenupro-settings-label">Primary Hover Color</label>
                                <div class="rmenupro-settings-control">
                                    <input type="color" name="rmenu_cart_hover_bg" value="<?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>" class="rmenu-color-picker" data-floating-cart-control="rmenu_cart_hover_bg" />
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-column">
                            <div class="rmenupro-settings-field plugincy_row items_center">
                                <label class="rmenupro-settings-label">Primary Hover Text Color</label>
                                <div class="rmenupro-settings-control">
                                    <input type="color" name="rmenu_cart_hover_text" value="<?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>" class="rmenu-color-picker" data-floating-cart-control="rmenu_cart_hover_text" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenupro-settings-row">
                        <div class="rmenupro-settings-field plugincy_row items_center">
                            <label class="rmenupro-settings-label">Floating Button Radius</label>
                            <div class="rmenu-settings-control">
                                <input type="text" name="rmenu_cart_border_radius" value="<?php echo esc_attr(get_option('rmenu_cart_border_radius', '5px 0 0 5px')); ?>" placeholder="0px 0px 0px 0px" data-floating-cart-control="rmenu_cart_border_radius" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?> mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-screenoptions"></span>', 'Drawer Elements', 'Manage which elements appear inside the floating cart drawer.'); ?>
                    <?php
                    $floating_cart_element_defaults = function_exists('onepaqucpro_get_floating_cart_element_defaults') ? onepaqucpro_get_floating_cart_element_defaults() : array();
                    $floating_cart_element_groups = array(
                        'Floating Button' => array(
                            'description' => 'Controls for the sticky cart button shown outside the drawer.',
                            'settings' => array(
                                'rmenu_floating_cart_show_cart_icon' => 'Cart Button Icon',
                                'rmenu_floating_cart_show_cart_count' => 'Cart Button Count',
                            ),
                        ),
                        'Cart Items' => array(
                            'description' => 'Controls for product rows, item actions, and product details.',
                            'settings' => array(
                                'rmenu_floating_cart_show_select_bar' => 'Select Bar',
                                'rmenu_floating_cart_show_item_select' => 'Item Checkbox',
                                'rmenu_floating_cart_show_remove_item' => 'Remove Item',
                                'rmenu_floating_cart_show_product_image' => 'Product Image',
                                'rmenu_floating_cart_show_product_title' => 'Product Title',
                                'rmenu_floating_cart_show_product_price' => 'Product Price',
                                'rmenu_floating_cart_show_quantity' => 'Quantity Controls',
                                'rmenu_floating_cart_show_variation_editor' => 'Variation Selector',
                                'rmenu_floating_cart_show_item_meta' => 'Cart Item Meta',
                                'rmenu_floating_cart_group_items' => 'Group Cart Items',
                            ),
                        ),
                        'Coupon' => array(
                            'description' => 'Controls for the coupon form and coupon collapse behavior.',
                            'settings' => array(
                                'rmenu_floating_cart_show_coupon' => 'Coupon Form',
                                'rmenu_floating_cart_show_coupon_title' => 'Coupon Title',
                                'rmenu_floating_cart_coupon_collapsible' => 'Collapsible Coupon',
                                'rmenu_floating_cart_coupon_initially_collapsed' => 'Coupon Initially Collapsed',
                            ),
                        ),
                        'Recommendations' => array(
                            'description' => 'Controls for suggested products shown inside the drawer.',
                            'settings' => array(
                                'rmenu_floating_cart_show_recommendations' => 'Related Products',
                            ),
                        ),
                        'Summary & Shipping' => array(
                            'description' => 'Controls for totals, shipping options, and summary collapse behavior.',
                            'settings' => array(
                                'rmenu_floating_cart_show_summary' => 'Cart Summary',
                                'rmenu_floating_cart_summary_collapsible' => 'Collapsible Summary',
                                'rmenu_floating_cart_summary_initially_collapsed' => 'Summary Initially Collapsed',
                                'rmenu_floating_cart_show_subtotal' => 'Subtotal Row',
                                'rmenu_floating_cart_show_discount' => 'Discount Row',
                                'rmenu_floating_cart_show_shipping_options' => 'Shipping Options',
                                'rmenu_floating_cart_show_shipping_total' => 'Shipping Row',
                                'rmenu_floating_cart_show_tax_total' => 'Tax Row',
                                'rmenu_floating_cart_show_total' => 'Total Row',
                            ),
                        ),
                        'Checkout & Empty Cart' => array(
                            'description' => 'Controls for the checkout action and empty-cart drawer state.',
                            'settings' => array(
                                'rmenu_floating_cart_show_checkout' => 'Checkout Button',
                                'rmenu_floating_cart_show_empty_icon' => 'Empty Cart Icon',
                                'rmenu_floating_cart_show_shop_button' => 'Shop Button',
                            ),
                        ),
                    );
                    ?>
                    <div class="onepaqucpro-floating-elements-groups">
                        <?php foreach ($floating_cart_element_groups as $group_title => $group_data) : ?>
                            <fieldset class="onepaqucpro-floating-elements-group">
                                <legend class="onepaqucpro-floating-elements-group__title"><?php echo esc_html($group_title); ?></legend>
                                <p class="onepaqucpro-floating-elements-group__description"><?php echo esc_html($group_data['description']); ?></p>
                                <div class="rmenupro-settings-row rmenupro-settings-row-columns onepaqucpro-floating-elements-group__controls">
                                    <?php foreach ($group_data['settings'] as $field_name => $field_label) : ?>
                                        <div class="rmenu-settings-column">
                                            <?php onepaqucpro_render_floating_cart_pro_switch($field_name, $field_label, isset($floating_cart_element_defaults[$field_name]) ? $floating_cart_element_defaults[$field_name] : '1'); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?> mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-index-card"></span>', 'Cart Item Data & Grouping', 'Control cart item meta visibility and optional item grouping.'); ?>
                    <div class="rmenupro-settings-row rmenupro-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <?php onepaqucpro_render_floating_cart_pro_switch('rmenu_floating_cart_show_variation_in_title', 'Show Variation in Product Title', isset($floating_cart_element_defaults['rmenu_floating_cart_show_variation_in_title']) ? $floating_cart_element_defaults['rmenu_floating_cart_show_variation_in_title'] : '1'); ?>
                        </div>
                    </div>
                    <div class="rmenupro-settings-row rmenupro-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <?php onepaqucpro_render_floating_cart_meta_rule_builder('rmenu_floating_cart_meta_include', 'Meta Include'); ?>
                        </div>
                    </div>
                    <div class="rmenupro-settings-row rmenupro-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenupro-settings-field plugincy_row items_center" data-floating-control-wrap="rmenu_floating_cart_group_by">
                                <label class="rmenupro-settings-label">Group Items By</label>
                                <div class="rmenupro-settings-control">
                                    <?php $floating_group_by = get_option('rmenu_floating_cart_group_by', 'category'); ?>
                                    <select name="rmenu_floating_cart_group_by" class="rmenu-select" data-floating-cart-control="rmenu_floating_cart_group_by" <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?>>
                                        <option value="category" <?php selected($floating_group_by, 'category'); ?>>Category</option>
                                        <option value="brand" <?php selected($floating_group_by, 'brand'); ?>>Brand</option>
                                        <option value="meta" <?php selected($floating_group_by, 'meta'); ?>>Cart Item Meta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="rmenu-settings-column">
                            <?php onepaqucpro_render_floating_cart_meta_picker('rmenu_floating_cart_group_meta_key', 'Group Meta Key', false); ?>
                        </div>
                    </div>
                    <div class="rmenupro-settings-row">
                        <div class="rmenupro-settings-field plugincy_row items_center" data-floating-control-wrap="rmenu_floating_cart_group_icon">
                            <label class="rmenupro-settings-label">Group Title Icon</label>
                            <div class="rmenupro-settings-control">
                                <?php $floating_group_icon = get_option('rmenu_floating_cart_group_icon', 'tag'); ?>
                                <select name="rmenu_floating_cart_group_icon" class="rmenu-select" data-floating-cart-control="rmenu_floating_cart_group_icon" <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?>>
                                    <option value="none" <?php selected($floating_group_icon, 'none'); ?>>No Icon</option>
                                    <option value="tag" <?php selected($floating_group_icon, 'tag'); ?>>Tag</option>
                                    <option value="folder" <?php selected($floating_group_icon, 'folder'); ?>>Folder</option>
                                    <option value="star" <?php selected($floating_group_icon, 'star'); ?>>Star</option>
                                    <option value="location" <?php selected($floating_group_icon, 'location'); ?>>Location</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?> mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-edit"></span>', 'Drawer Text', 'Manage text used inside the floating cart drawer.'); ?>
                    <?php
                    $floating_cart_text_defaults = function_exists('onepaqucpro_get_floating_cart_text_defaults') ? onepaqucpro_get_floating_cart_text_defaults() : array();
                    $floating_cart_text_groups = array(
                        'Drawer Header & Selection' => array(
                            'description' => 'Labels shown in the drawer header and selection bar.',
                            'settings' => array(
                                'your_cart' => 'Drawer Title',
                                'txt_Select_All' => 'Select All Label',
                                'txt_Selected' => 'Selected Count Suffix',
                            ),
                        ),
                        'Empty Cart' => array(
                            'description' => 'Text shown when the floating cart drawer has no products.',
                            'settings' => array(
                                'rmenu_floating_cart_empty_title' => 'Empty Cart Title',
                                'rmenu_floating_cart_shop_button_text' => 'Shop Button',
                            ),
                        ),
                        'Coupon' => array(
                            'description' => 'Coupon section labels and applied-coupon controls.',
                            'settings' => array(
                                'rmenu_floating_cart_coupon_title' => 'Coupon Title',
                                'rmenu_floating_cart_coupon_placeholder' => 'Coupon Placeholder',
                                'rmenu_floating_cart_coupon_button_text' => 'Coupon Button',
                                'rmenu_floating_cart_applied_coupons_heading' => 'Applied Coupons Heading',
                                'rmenu_floating_cart_remove_coupon_text' => 'Remove Coupon',
                            ),
                        ),
                        'Variation Editor' => array(
                            'description' => 'Button text used when changing a variable product inside the drawer.',
                            'settings' => array(
                                'rmenu_floating_cart_variation_update_text' => 'Variation Update Button',
                                'rmenu_floating_cart_variation_cancel_text' => 'Variation Cancel Button',
                            ),
                        ),
                        'Recommendations' => array(
                            'description' => 'Text used for related products in the floating cart drawer.',
                            'settings' => array(
                                'txt_you_may_like' => 'Related Products Heading',
                                'rmenu_floating_cart_related_add_to_cart_text' => 'Add to Cart Button',
                            ),
                        ),
                        'Summary & Checkout' => array(
                            'description' => 'Labels used for totals, shipping, tax, and the checkout action.',
                            'settings' => array(
                                'txt_subtotal' => 'Subtotal Label',
                                'rmenu_floating_cart_discount_label' => 'Discount Label',
                                'rmenu_floating_cart_shipping_options_label' => 'Shipping Options Heading',
                                'rmenu_floating_cart_shipping_label' => 'Shipping Label',
                                'rmenu_floating_cart_tax_label' => 'Tax Label',
                                'txt_total' => 'Total Label',
                                'txt_checkout' => 'Checkout Button',
                            ),
                        ),
                    );
                    ?>
                    <div class="onepaqucpro-floating-text-groups">
                        <?php foreach ($floating_cart_text_groups as $group_title => $group_data) : ?>
                            <fieldset class="onepaqucpro-floating-text-group">
                                <legend class="onepaqucpro-floating-text-group__title"><?php echo esc_html($group_title); ?></legend>
                                <p class="onepaqucpro-floating-text-group__description"><?php echo esc_html($group_data['description']); ?></p>
                                <div class="plugincy_grid onepaqucpro-floating-text-group__fields">
                                    <?php foreach ($group_data['settings'] as $field_name => $field_label) : ?>
                                        <?php onepaqucpro_render_floating_cart_pro_text_field($field_name, $field_label, isset($floating_cart_text_defaults[$field_name]) ? $floating_cart_text_defaults[$field_name] : ''); ?>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-megaphone"></span>', 'Drawer Notices & Feedback Text', 'Customize coupon, cart item, quantity, and drawer variation messages used by the floating cart.'); ?>
                    <?php
                    $floating_cart_feedback_text_groups = array(
                        'General Status' => array(
                            'description' => 'Shared loading, removing, and error messages used by drawer actions.',
                            'settings' => array(
                                'rmenu_floating_cart_applying_text' => 'Generic Loading Text',
                                'rmenu_floating_cart_error_try_again_message' => 'Generic Error Message',
                                'rmenu_floating_cart_removing_text' => 'Generic Removing Text',
                                'rmenu_floating_cart_cart_unavailable_message' => 'Cart Unavailable Message',
                            ),
                        ),
                        'Coupon Feedback' => array(
                            'description' => 'Messages shown while applying or removing coupons in the drawer.',
                            'settings' => array(
                                'rmenu_floating_cart_applying_coupon_text' => 'Applying Coupon Message',
                                'rmenu_floating_cart_coupon_applied_message' => 'Coupon Applied Message',
                                'rmenu_floating_cart_invalid_coupon_message' => 'Invalid Coupon Message',
                                'rmenu_floating_cart_removing_coupon_text' => 'Removing Coupon Message',
                                'rmenu_floating_cart_coupon_removed_message' => 'Coupon Removed Message',
                                'rmenu_floating_cart_failed_remove_coupon_message' => 'Coupon Remove Error Message',
                            ),
                        ),
                        'Variation Feedback' => array(
                            'description' => 'Messages for drawer variation selection and update flows.',
                            'settings' => array(
                                'rmenu_floating_cart_select_variation_error_message' => 'Variation Selection Error',
                                'rmenu_floating_cart_update_variation_error_message' => 'Variation Update Error',
                                'rmenu_floating_cart_variation_disabled_message' => 'Variation Disabled Message',
                                'rmenu_floating_cart_item_not_variable_message' => 'Not Variable Item Message',
                                'rmenu_floating_cart_invalid_variation_message' => 'Invalid Variation Message',
                                'rmenu_floating_cart_same_variation_message' => 'Same Variation Message',
                                'rmenu_floating_cart_unavailable_variation_message' => 'Unavailable Variation Message',
                                'rmenu_floating_cart_original_item_update_error_message' => 'Original Item Update Error',
                                'rmenu_floating_cart_variation_updated_message' => 'Variation Updated Message',
                            ),
                        ),
                        'Cart Item & Quantity Feedback' => array(
                            'description' => 'Messages for cart item lookup, quantity changes, and item removal.',
                            'settings' => array(
                                'rmenu_floating_cart_item_not_found_message' => 'Cart Item Missing Message',
                                'rmenu_floating_cart_quantity_update_error_message' => 'Quantity Update Error',
                                'rmenu_floating_cart_no_cart_item_key_message' => 'Missing Cart Item Key Message',
                                'rmenu_floating_cart_remove_items_error_message' => 'Remove Items Error',
                            ),
                        ),
                        'Accessibility Labels' => array(
                            'description' => 'Labels used by screen readers for item and quantity controls.',
                            'settings' => array(
                                'rmenu_floating_cart_remove_item_aria_label' => 'Remove Item Accessibility Label',
                                'rmenu_floating_cart_decrease_quantity_label' => 'Decrease Quantity Accessibility Label',
                                'rmenu_floating_cart_increase_quantity_label' => 'Increase Quantity Accessibility Label',
                            ),
                        ),
                    );
                    ?>
                    <div class="onepaqucpro-floating-text-groups">
                        <?php foreach ($floating_cart_feedback_text_groups as $group_title => $group_data) : ?>
                            <fieldset class="onepaqucpro-floating-text-group">
                                <legend class="onepaqucpro-floating-text-group__title"><?php echo esc_html($group_title); ?></legend>
                                <p class="onepaqucpro-floating-text-group__description"><?php echo esc_html($group_data['description']); ?></p>
                                <div class="plugincy_grid onepaqucpro-floating-text-group__fields">
                                    <?php foreach ($group_data['settings'] as $field_name => $field_label) : ?>
                                        <?php onepaqucpro_render_floating_cart_pro_text_field($field_name, $field_label, isset($floating_cart_text_defaults[$field_name]) ? $floating_cart_text_defaults[$field_name] : ''); ?>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>
                </div>

                <div class="onepaqucpro-floating-editor-right" aria-label="<?php esc_attr_e('Floating cart visual preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    <?php
                    $floating_preview_text = array(
                        'your_cart' => onepaqucpro_get_floating_cart_admin_preview_text('your_cart', $floating_cart_text_defaults, 'Your Cart'),
                        'txt_Select_All' => onepaqucpro_get_floating_cart_admin_preview_text('txt_Select_All', $floating_cart_text_defaults, 'Select All'),
                        'txt_Selected' => onepaqucpro_get_floating_cart_admin_preview_text('txt_Selected', $floating_cart_text_defaults, 'selected'),
                        'rmenu_floating_cart_empty_title' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_empty_title', $floating_cart_text_defaults, 'Your Cart is Empty'),
                        'rmenu_floating_cart_shop_button_text' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_shop_button_text', $floating_cart_text_defaults, 'Shop Now'),
                        'rmenu_floating_cart_coupon_title' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_coupon_title', $floating_cart_text_defaults, 'Have a coupon?'),
                        'rmenu_floating_cart_coupon_placeholder' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_coupon_placeholder', $floating_cart_text_defaults, 'Enter coupon code'),
                        'rmenu_floating_cart_coupon_button_text' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_coupon_button_text', $floating_cart_text_defaults, 'Apply'),
                        'rmenu_floating_cart_variation_update_text' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_variation_update_text', $floating_cart_text_defaults, 'Update'),
                        'rmenu_floating_cart_variation_cancel_text' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_variation_cancel_text', $floating_cart_text_defaults, 'Cancel'),
                        'txt_you_may_like' => onepaqucpro_get_floating_cart_admin_preview_text('txt_you_may_like', $floating_cart_text_defaults, 'You may also like'),
                        'rmenu_floating_cart_related_add_to_cart_text' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_related_add_to_cart_text', $floating_cart_text_defaults, 'Add to cart'),
                        'txt_subtotal' => onepaqucpro_get_floating_cart_admin_preview_text('txt_subtotal', $floating_cart_text_defaults, 'Subtotal'),
                        'rmenu_floating_cart_discount_label' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_discount_label', $floating_cart_text_defaults, 'Discount'),
                        'rmenu_floating_cart_shipping_options_label' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_shipping_options_label', $floating_cart_text_defaults, 'Shipping options'),
                        'rmenu_floating_cart_shipping_label' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_shipping_label', $floating_cart_text_defaults, 'Shipping'),
                        'rmenu_floating_cart_tax_label' => onepaqucpro_get_floating_cart_admin_preview_text('rmenu_floating_cart_tax_label', $floating_cart_text_defaults, 'Tax'),
                        'txt_total' => onepaqucpro_get_floating_cart_admin_preview_text('txt_total', $floating_cart_text_defaults, 'Total'),
                        'txt_checkout' => onepaqucpro_get_floating_cart_admin_preview_text('txt_checkout', $floating_cart_text_defaults, 'Checkout'),
                    );
                    $floating_preview_meta_rules = function_exists('onepaqucpro_get_floating_cart_meta_rules_option')
                        ? onepaqucpro_get_floating_cart_meta_rules_option('rmenu_floating_cart_meta_include')
                        : array();
                    $floating_preview_variation_in_title = get_option('rmenu_floating_cart_show_variation_in_title', isset($floating_cart_element_defaults['rmenu_floating_cart_show_variation_in_title']) ? $floating_cart_element_defaults['rmenu_floating_cart_show_variation_in_title'] : '1') === '1';
                    $floating_preview_product_title = 'B100 Pro Wireless Gaming Headset';
                    $floating_preview_variation_title = 'Black';
                    $floating_preview_show_coupon_title = get_option('rmenu_floating_cart_show_coupon_title', isset($floating_cart_element_defaults['rmenu_floating_cart_show_coupon_title']) ? $floating_cart_element_defaults['rmenu_floating_cart_show_coupon_title'] : '1') === '1';
                    $floating_preview_coupon_collapsible = get_option('rmenu_floating_cart_coupon_collapsible', isset($floating_cart_element_defaults['rmenu_floating_cart_coupon_collapsible']) ? $floating_cart_element_defaults['rmenu_floating_cart_coupon_collapsible'] : '0') === '1';
                    $floating_preview_coupon_initially_collapsed = $floating_preview_coupon_collapsible && get_option('rmenu_floating_cart_coupon_initially_collapsed', isset($floating_cart_element_defaults['rmenu_floating_cart_coupon_initially_collapsed']) ? $floating_cart_element_defaults['rmenu_floating_cart_coupon_initially_collapsed'] : '0') === '1';
                    $floating_preview_coupon_classes = array('onepaqucpro-floating-preview-coupon');
                    if ($floating_preview_coupon_collapsible) {
                        $floating_preview_coupon_classes[] = 'is-coupon-collapsible';
                        if ($floating_preview_coupon_initially_collapsed) {
                            $floating_preview_coupon_classes[] = 'is-coupon-collapsed';
                        }
                    }
                    $floating_preview_bg = get_option('rmenu_cart_bg_color', '#96588a');
                    $floating_preview_text_color = get_option('rmenu_cart_text_color', '#ffffff');
                    $floating_preview_radius = get_option('rmenu_cart_border_radius', '5px 0 0 5px');
                    $floating_preview_icon_classes = array(
                        'cart' => 'dashicons-cart',
                        'shopping-bag' => 'dashicons-store',
                        'basket' => 'dashicons-products',
                    );
                    $floating_preview_icon = get_option('rmenu_floating_cart_icon', 'cart');
                    $floating_preview_icon_class = isset($floating_preview_icon_classes[$floating_preview_icon]) ? $floating_preview_icon_classes[$floating_preview_icon] : $floating_preview_icon_classes['cart'];
                    $floating_preview_empty_icon = function_exists('onepaqucpro_get_floating_cart_empty_icon') ? onepaqucpro_get_floating_cart_empty_icon() : 'cart';
                    $floating_preview_empty_icon_svg = function_exists('onepaqucpro_get_floating_cart_empty_icon_svg') ? onepaqucpro_get_floating_cart_empty_icon_svg($floating_preview_empty_icon) : '';
                    $floating_preview_svg_allowed = function_exists('onepaqucpro_get_floating_cart_svg_allowed_html') ? onepaqucpro_get_floating_cart_svg_allowed_html() : array();
                    ?>
                    <div class="onepaqucpro-floating-preview-toolbar">
                        <div>
                            <h3><?php esc_html_e('Visual Cart Editor', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <p><?php esc_html_e('Edit text directly, use the eye to show or hide elements, and use the pen for more settings.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                        <div class="onepaqucpro-floating-preview-modes" role="group" aria-label="<?php esc_attr_e('Preview mode', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            <button type="button" class="button active" data-floating-preview-mode="cart"><?php esc_html_e('Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                            <button type="button" class="button" data-floating-preview-mode="empty"><?php esc_html_e('Empty', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        </div>
                    </div>

                    <div class="onepaqucpro-floating-preview-stage" style="--onepaqucpro-floating-primary: <?php echo esc_attr($floating_preview_bg); ?>; --onepaqucpro-floating-primary-text: <?php echo esc_attr($floating_preview_text_color); ?>; --onepaqucpro-floating-primary-hover: <?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>; --onepaqucpro-floating-primary-hover-text: <?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>; --onepaqucpro-floating-button-radius: <?php echo esc_attr($floating_preview_radius); ?>;">
                        <button type="button" class="onepaqucpro-floating-preview-button" data-preview-target="rmenu_floating_cart_show_cart_icon">
                            <span class="dashicons <?php echo esc_attr($floating_preview_icon_class); ?>" data-preview-part="rmenu_floating_cart_show_cart_icon" data-preview-icon="cart"></span>
                            <span class="onepaqucpro-floating-preview-count" data-preview-part="rmenu_floating_cart_show_cart_count">2</span>
                        </button>

                        <div class="onepaqucpro-floating-preview-drawer" data-floating-preview-panel="cart">
                            <div class="onepaqucpro-floating-preview-header" data-preview-target="your_cart">
                                <h2 data-preview-text="your_cart"><?php echo esc_html($floating_preview_text['your_cart']); ?></h2>
                                <span class="dashicons dashicons-no-alt"></span>
                            </div>
                            <div class="onepaqucpro-floating-preview-select" data-preview-part="rmenu_floating_cart_show_select_bar" data-preview-target="rmenu_floating_cart_show_select_bar">
                                <span><input type="checkbox" checked> <span data-preview-text="txt_Select_All"><?php echo esc_html($floating_preview_text['txt_Select_All']); ?></span></span>
                                <span>2 <span data-preview-text="txt_Selected"><?php echo esc_html($floating_preview_text['txt_Selected']); ?></span></span>
                            </div>
                            <div class="onepaqucpro-floating-preview-group" data-preview-part="rmenu_floating_cart_group_items" data-preview-target="rmenu_floating_cart_group_items">
                                <h4><span data-preview-group-icon>●</span><span data-preview-group-title>Gaming Accessories</span></h4>
                            </div>
                            <div class="onepaqucpro-floating-preview-item">
                                <input type="checkbox" checked data-preview-part="rmenu_floating_cart_show_item_select" data-preview-target="rmenu_floating_cart_show_item_select">
                                <button type="button" class="onepaqucpro-floating-preview-remove" data-preview-part="rmenu_floating_cart_show_remove_item" data-preview-target="rmenu_floating_cart_show_remove_item">&times;</button>
                                <div class="onepaqucpro-floating-preview-image" data-preview-part="rmenu_floating_cart_show_product_image" data-preview-target="rmenu_floating_cart_show_product_image"><span class="dashicons dashicons-format-image"></span></div>
                                <div class="onepaqucpro-floating-preview-item-main">
                                    <div class="onepaqucpro-floating-preview-title" data-preview-part="rmenu_floating_cart_show_product_title" data-preview-target="rmenu_floating_cart_show_product_title" data-preview-variation-title data-preview-title-base="<?php echo esc_attr($floating_preview_product_title); ?>" data-preview-title-variation="<?php echo esc_attr($floating_preview_variation_title); ?>"><?php echo esc_html($floating_preview_variation_in_title ? $floating_preview_product_title . ' - ' . $floating_preview_variation_title : $floating_preview_product_title); ?></div>
                                    <dl class="onepaqucpro-floating-preview-meta" data-preview-part="rmenu_floating_cart_show_item_meta" data-preview-target="rmenu_floating_cart_show_item_meta" data-preview-meta-list>
                                        <?php $floating_preview_rendered_meta = false; ?>
                                        <?php if (!empty($floating_preview_meta_rules)) : ?>
                                            <?php foreach ($floating_preview_meta_rules as $floating_preview_meta_rule) : ?>
                                                <?php if (function_exists('onepaqucpro_is_floating_cart_variations_meta_key') && isset($floating_preview_meta_rule['key']) && onepaqucpro_is_floating_cart_variations_meta_key($floating_preview_meta_rule['key'])) : ?>
                                                    <?php if (!$floating_preview_variation_in_title && isset($floating_preview_meta_rule['mode']) && $floating_preview_meta_rule['mode'] === 'combine') : ?>
                                                        <?php $floating_preview_rendered_meta = true; ?>
                                                        <div data-preview-variation-meta>
                                                            <dt><?php echo esc_html(isset($floating_preview_meta_rule['title']) ? $floating_preview_meta_rule['title'] : __('Variations', 'one-page-quick-checkout-for-woocommerce-pro')); ?></dt>
                                                            <dd><?php esc_html_e('Black, S', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dd>
                                                        </div>
                                                    <?php elseif (!$floating_preview_variation_in_title) : ?>
                                                        <?php $floating_preview_rendered_meta = true; ?>
                                                        <div data-preview-variation-meta>
                                                            <dt><?php esc_html_e('Color', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                            <dd><?php echo esc_html($floating_preview_variation_title); ?></dd>
                                                        </div>
                                                        <div data-preview-variation-meta>
                                                            <dt><?php esc_html_e('Size', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                            <dd><?php esc_html_e('S', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dd>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <?php $floating_preview_rendered_meta = true; ?>
                                                    <div>
                                                        <dt><?php echo esc_html(isset($floating_preview_meta_rule['title']) ? $floating_preview_meta_rule['title'] : 'Meta'); ?></dt>
                                                        <dd><?php echo esc_html(onepaqucpro_get_floating_cart_admin_meta_preview_value(isset($floating_preview_meta_rule['key']) ? $floating_preview_meta_rule['key'] : '')); ?></dd>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if (!$floating_preview_rendered_meta && $floating_preview_variation_in_title) : ?>
                                            <div class="onepaqucpro-floating-preview-meta-placeholder"><?php esc_html_e('No meta data selected. Add Meta Include to preview cart item data.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></div>
                                        <?php endif; ?>
                                    </dl>
                                    <div class="onepaqucpro-floating-preview-actions">
                                        <div class="onepaqucpro-floating-preview-qty" data-preview-part="rmenu_floating_cart_show_quantity" data-preview-target="rmenu_floating_cart_show_quantity"><button type="button">-</button><span>1</span><button type="button">+</button></div>
                                        <div class="onepaqucpro-floating-preview-variation-editor" data-preview-part="rmenu_floating_cart_show_variation_editor" data-preview-target="rmenu_floating_cart_show_variation_editor">
                                            <button type="button" class="onepaqucpro-floating-preview-variation-toggle" aria-expanded="false">
                                                <span class="dashicons dashicons-update"></span>
                                            </button>
                                            <div class="onepaqucpro-floating-preview-variation-panel">
                                                <label>
                                                    <span><?php esc_html_e('Color', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <select>
                                                        <option><?php esc_html_e('Black', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                                        <option><?php esc_html_e('Blue', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                                    </select>
                                                </label>
                                                <label>
                                                    <span><?php esc_html_e('Size', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <select>
                                                        <option><?php esc_html_e('Standard', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                                        <option><?php esc_html_e('Large', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                                    </select>
                                                </label>
                                                <div class="onepaqucpro-floating-preview-variation-actions">
                                                    <button type="button" class="onepaqucpro-floating-preview-variation-update" data-preview-text="rmenu_floating_cart_variation_update_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_variation_update_text']); ?></button>
                                                    <button type="button" class="onepaqucpro-floating-preview-variation-cancel" data-preview-text="rmenu_floating_cart_variation_cancel_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_variation_cancel_text']); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <strong data-preview-part="rmenu_floating_cart_show_product_price" data-preview-target="rmenu_floating_cart_show_product_price">500.00&#2547;</strong>
                            </div>
                            <div class="<?php echo esc_attr(implode(' ', $floating_preview_coupon_classes)); ?>" data-preview-part="rmenu_floating_cart_show_coupon" data-preview-target="rmenu_floating_cart_show_coupon" data-preview-coupon>
                                <button type="button" class="onepaqucpro-floating-preview-coupon-toggle" data-preview-coupon-toggle aria-expanded="<?php echo $floating_preview_coupon_initially_collapsed ? 'false' : 'true'; ?>" <?php echo (!$floating_preview_coupon_collapsible && !$floating_preview_show_coupon_title) ? 'hidden' : ''; ?>>
                                    <span data-preview-part="rmenu_floating_cart_show_coupon_title" data-preview-target="rmenu_floating_cart_show_coupon_title" data-preview-text="rmenu_floating_cart_coupon_title" <?php echo !$floating_preview_show_coupon_title ? 'hidden' : ''; ?>><?php echo esc_html($floating_preview_text['rmenu_floating_cart_coupon_title']); ?></span>
                                    <span class="onepaqucpro-floating-preview-coupon-toggle__icon" aria-hidden="true"></span>
                                </button>
                                <div class="onepaqucpro-floating-preview-coupon-content" data-preview-coupon-content aria-hidden="<?php echo $floating_preview_coupon_initially_collapsed ? 'true' : 'false'; ?>">
                                    <div class="onepaqucpro-floating-preview-coupon-form">
                                        <span data-preview-text="rmenu_floating_cart_coupon_placeholder"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_coupon_placeholder']); ?></span>
                                        <button type="button" data-preview-text="rmenu_floating_cart_coupon_button_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_coupon_button_text']); ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="onepaqucpro-floating-preview-related" data-preview-part="rmenu_floating_cart_show_recommendations" data-preview-target="rmenu_floating_cart_show_recommendations">
                                <h4 data-preview-text="txt_you_may_like"><?php echo esc_html($floating_preview_text['txt_you_may_like']); ?></h4>
                                <div class="onepaqucpro-floating-preview-products">
                                    <div><span class="dashicons dashicons-format-image"></span><p>Fastest MacBook</p><strong>650.00&#2547;</strong><button type="button" data-preview-text="rmenu_floating_cart_related_add_to_cart_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_related_add_to_cart_text'] ? $floating_preview_text['rmenu_floating_cart_related_add_to_cart_text'] : 'Add to cart'); ?></button></div>
                                    <div><span class="dashicons dashicons-format-image"></span><p>Desktop PC Core i5</p><strong>570.00&#2547;</strong><button type="button" data-preview-text="rmenu_floating_cart_related_add_to_cart_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_related_add_to_cart_text'] ? $floating_preview_text['rmenu_floating_cart_related_add_to_cart_text'] : 'Add to cart'); ?></button></div>
                                </div>
                            </div>
                            <div class="onepaqucpro-floating-preview-shipping" data-preview-part="rmenu_floating_cart_show_shipping_options" data-preview-target="rmenu_floating_cart_show_shipping_options">
                                <h4 data-preview-text="rmenu_floating_cart_shipping_options_label"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_shipping_options_label']); ?></h4>
                                <label><input type="radio" checked> Flat rate: 60.00&#2547;</label>
                                <label><input type="radio"> Local pickup</label>
                            </div>
                            <div class="onepaqucpro-floating-preview-summary" data-preview-part="rmenu_floating_cart_show_summary" data-preview-target="rmenu_floating_cart_show_summary">
                                <div class="onepaqucpro-floating-preview-summary-content" data-preview-summary-content>
                                    <div data-preview-part="rmenu_floating_cart_show_subtotal" data-preview-target="rmenu_floating_cart_show_subtotal"><span data-preview-text="txt_subtotal"><?php echo esc_html($floating_preview_text['txt_subtotal']); ?></span><span>1,150.00&#2547;</span></div>
                                    <div data-preview-part="rmenu_floating_cart_show_discount" data-preview-target="rmenu_floating_cart_show_discount"><span data-preview-text="rmenu_floating_cart_discount_label"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_discount_label']); ?></span><span>- 50.00&#2547;</span></div>
                                    <div data-preview-part="rmenu_floating_cart_show_shipping_total" data-preview-target="rmenu_floating_cart_show_shipping_total"><span data-preview-text="rmenu_floating_cart_shipping_label"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_shipping_label']); ?></span><span>60.00&#2547;</span></div>
                                    <div data-preview-part="rmenu_floating_cart_show_tax_total" data-preview-target="rmenu_floating_cart_show_tax_total"><span data-preview-text="rmenu_floating_cart_tax_label"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_tax_label']); ?></span><span>50.00&#2547;</span></div>
                                </div>
                                <button type="button" class="onepaqucpro-floating-preview-summary-toggle total" data-preview-part="rmenu_floating_cart_show_total" data-preview-target="rmenu_floating_cart_summary_collapsible" data-preview-summary-toggle aria-label="<?php esc_attr_e('Toggle cart summary', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" hidden>
                                    <span data-preview-text="txt_total"><?php echo esc_html($floating_preview_text['txt_total']); ?></span>
                                    <strong>1,210.00&#2547;</strong>
                                    <span aria-hidden="true"></span>
                                </button>
                            </div>
                            <button type="button" class="onepaqucpro-floating-preview-checkout" data-preview-part="rmenu_floating_cart_show_checkout" data-preview-target="rmenu_floating_cart_show_checkout" data-preview-text="txt_checkout"><?php echo esc_html($floating_preview_text['txt_checkout']); ?></button>
                        </div>

                        <div class="onepaqucpro-floating-preview-drawer onepaqucpro-floating-preview-empty" data-floating-preview-panel="empty" hidden>
                            <div class="onepaqucpro-floating-preview-header" data-preview-target="your_cart">
                                <h2 data-preview-text="your_cart"><?php echo esc_html($floating_preview_text['your_cart']); ?></h2>
                                <span class="dashicons dashicons-no-alt"></span>
                            </div>
                            <div class="onepaqucpro-floating-preview-empty-body">
                                <span class="onepaqucpro-floating-preview-empty-icon" data-preview-part="rmenu_floating_cart_show_empty_icon" data-preview-target="rmenu_floating_cart_show_empty_icon" data-preview-empty-icon>
                                    <?php echo wp_kses($floating_preview_empty_icon_svg, $floating_preview_svg_allowed); ?>
                                </span>
                                <p data-preview-text="rmenu_floating_cart_empty_title"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_empty_title']); ?></p>
                                <button type="button" data-preview-part="rmenu_floating_cart_show_shop_button" data-preview-target="rmenu_floating_cart_show_shop_button" data-preview-text="rmenu_floating_cart_shop_button_text"><?php echo esc_html($floating_preview_text['rmenu_floating_cart_shop_button_text']); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-admin-settings"></span> Additional Settings(Coming Soon)</h3>
                    </div>

                    <div class="rmenupro-settings-row">
                        <div class="rmenupro-settings-field">
                            <label class="rmenupro-settings-label">Show Cart Icon</label>
                            <div class="rmenupro-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_show_cart_icon" value="1" <?php //checked(1, get_option("rmenu_show_cart_icon", 1), true); 
                                                                                                    ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rmenupro-settings-row">
                        <div class="rmenupro-settings-field">
                            <label class="rmenupro-settings-label">Show Cart Count</label>
                            <div class="rmenupro-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_show_cart_count" value="1" <?php //checked(1, get_option("rmenu_show_cart_count", 1), true); 
                                                                                                    ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div> -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // if the "Enable floating cart" checkbox is checked
                        const enableCheckout = document.querySelector('div#tab-9 input[name="rmenu_enable_sticky_cart"]');

                        const allinputFields = Array.from(document.querySelectorAll('div#tab-9 input,div#tab-9 select')).filter(
                            el => !(el.name === "rmenu_enable_sticky_cart")
                        );
                        toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                        enableCheckout.addEventListener('change', function() {
                            toggleDisabledClass(!this.checked, allinputFields);
                        });
                    });
                </script>
            </div>
            <div class="tab-content active" id="tab-4">
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" xml:space="preserve" width="16" height="16">
                                <path d="M13.984 5.25a.73.73 0 0 0-.65-.402H9.662l1.898-3.796A.727.727 0 0 0 10.909 0H6.545a.73.73 0 0 0-.65.402L2.016 8.16a.727.727 0 0 0 .65 1.052h3.949L5.349 15.12a.726.726 0 0 0 .41.814.73.73 0 0 0 .883-.226l7.273-9.697a.73.73 0 0 0 .069-.762" />
                            </svg>', 'WooCommerce Direct Checkout', '', 'Enable direct checkout to allow customers to bypass the cart and proceed directly to checkout, streamlining the purchasing process.'); ?>


                    <div class="rmenupro-settings-tabs">
                        <ul class="rmenupro-settings-tab-list">
                            <li class="rmenupro-settings-tab-item active" data-tab="direct-general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenupro-settings-tab-item" data-tab="direct-advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="direct-general-settings" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>
                            <table class="form-table plugincy_table">
                                <tr valign="top">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Direct Checkout', 'Enable or disable the direct checkout functionality across your WooCommerce store.'); ?>
                                    <td><?php $onepaquc_helper->switcher('rmenupro_add_direct_checkout_button'); ?></td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Text', 'Customize the text displayed on the direct checkout button.'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        $direct_checkout_text = get_option('txt-direct-checkout', '');
                                        if (empty($direct_checkout_text)) {
                                            $direct_checkout_text = 'Buy Now';
                                        }
                                        ?>
                                        <input type="text" name="txt-direct-checkout" value="<?php echo esc_attr($direct_checkout_text); ?>"  />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position on Archive Page', 'Choose where to display the direct checkout button on product pages.'); ?>
                                    <td>
                                        <div class="rmenupro-settings-control">
                                            <select name="rmenupro_wc_direct_checkout_position" class="rmenupro-select">
                                                <option value="overlay_thumbnail" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'overlay_thumbnail'); ?>>Overlay on Product Image</option>
                                                <option value="overlay_thumbnail_hover" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'overlay_thumbnail_hover'); ?>>Overlay on Product Image Hover</option>
                                                <option value="after_product" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product'); ?>>After Product</option>
                                                <option value="after_product_title" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_title'); ?>>After Product Title</option>
                                                <option value="before_product_title" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'before_product_title'); ?>>Before Product Title</option>
                                                <option value="after_product_excerpt" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_excerpt'); ?>>After Product Excerpt</option>
                                                <option value="after_product_rating" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_rating'); ?>>After Product Rating</option>
                                                <option value="after_product_price" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_price'); ?>>After Product Price</option>
                                                <option value="after_add_to_cart" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                                <option value="before_add_to_cart" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'before_add_to_cart'); ?>>Before Add to Cart Button</option>
                                                <option value="bottom_add_to_cart" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'bottom_add_to_cart'); ?>>Bottom of Add to Cart Button</option>
                                                <option value="replace_add_to_cart" <?php selected(get_option('rmenupro_wc_direct_checkout_position', 'after_add_to_cart'), 'replace_add_to_cart'); ?>>Replace Add to Cart Button</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position on Single Page', 'Choose where to display the direct checkout button on single pages.'); ?>
                                    </th>
                                    <td>
                                        <div class="rmenupro-settings-control">
                                            <select name="rmenu_wc_direct_checkout_single_position" class="rmenu-select">
                                                <option value="after_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                                <option value="before_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'before_add_to_cart'); ?>>Before Add to Cart Button</option>
                                                <option value="bottom_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'bottom_add_to_cart'); ?>>Bottom of Add to Cart Button</option>
                                                <option value="replace_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'replace_add_to_cart'); ?>>Replace Add to Cart Button</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenupro-settings-section plugincy_col-5 plugincy_card" id="rmenupro-direct-button-display-section">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Display Settings', ''); ?>


                            <div class="rmenupro-settings-row">
                                <div class="rmenupro-settings-field">
                                    <label class="rmenupro-settings-label">Product Types</label>
                                    <?php $product_types_option = get_option('rmenupro_show_quick_checkout_by_types', ["simple", "variable", "external"]); ?>
                                    <div class="rmenupro-settings-control rmenupro-checkbox-group">
                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_checkout_by_types[]" value="simple" <?php checked(in_array('simple', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">Simple Products</span>
                                        </label>

                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_checkout_by_types[]" value="variable" <?php checked(in_array('variable', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">Variable Products</span>
                                        </label>

                                        <!-- <label class="rmenupro-checkbox-container <?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                                                        ?>">
                                        <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                                ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_show_quick_checkout_by_types[]' : 'rmenupro_show_quick_checkout_by_types[]'; 
                                                                            ?>" value="coming_grouped" <?php //checked(in_array('grouped', $product_types_option)); 
                                                                                                        ?> />
                                        <span class="rmenupro-checkbox-label">Grouped Products (Coming Soon)</span>
                                    </label> -->

                                        <!-- <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_checkout_by_types[]" value="external" <?php //checked(in_array('external', $product_types_option)); 
                                                                                                                                    ?> />
                                            <span class="rmenupro-checkbox-label">External/Affiliate Products</span>
                                        </label> -->
                                    </div>
                                </div>
                            </div>

                            <div class="rmenupro-settings-row">
                                <div class="rmenupro-settings-field">
                                    <?php $product_types_option = get_option('rmenupro_show_quick_checkout_by_page', ["single", "related", "upsells", "shop-page", "category-archives", "tag-archives", "featured-products", "on-sale", "recent", "widgets", "shortcodes"]); ?>
                                    <div class="rmenupro-settings-control rmenupro-checkbox-group">
                                        <div class="rmenupro-checkbox-column">
                                            <h4>Product Pages</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="single" <?php checked(in_array('single', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Single Product Pages</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="upsells" <?php checked(in_array('upsells', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Upsells</span>
                                            </label>

                                            <!-- <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="cross-sells" <?php //checked(in_array('cross-sells', $product_types_option)); 
                                                                                                                                        ?> />
                                            <span class="rmenupro-checkbox-label">Cross-sells (Coming Soon)</span>
                                        </label> -->
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <h4>Archive Pages</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="shop-page" <?php checked(in_array('shop-page', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Main Shop Page</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="category-archives" <?php checked(in_array('category-archives', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Category Archives</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="tag-archives" <?php checked(in_array('tag-archives', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Tag Archives</span>
                                            </label>
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <h4>Others</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="featured-products" <?php checked(in_array('featured-products', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Featured Products</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="on-sale" <?php checked(in_array('on-sale', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">On-Sale Products</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="recent" <?php checked(in_array('recent', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Recent Products</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_checkout_by_page[]" value="shortcodes" <?php checked(in_array('shortcodes', $product_types_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Other Pages</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="plugincy_row">
                        <div class="plugincy_col-5 items_center  space_between plugincy_card">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-cart"></span>', 'Quick Checkout Behavior', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Checkout Method', 'Choose how the quick checkout process should behave when a customer clicks the button.'); ?>
                                    </th>
                                    <td class="rmenupro-settings-control">
                                        <select name="rmenupro_wc_checkout_method" class="rmenupro-select" id="rmenupro-checkout-method">
                                            <option value="direct_checkout" <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'direct_checkout'); ?>>Redirect to Checkout</option>
                                            <option value="ajax_add" <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'ajax_add'); ?>>AJAX Add to Cart</option>
                                            <!-- rmenupro_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                            <?php
                                            $disable_cart_page = get_option('rmenupro_disable_cart_page', '0');
                                            ?>
                                            <?php if (!$disable_cart_page) : ?>
                                                <option value="cart_redirect" <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page</option>
                                            <?php else : ?>
                                                <option value="cart_redirect" disabled <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page (Disabled)</option>
                                            <?php endif; ?>
                                            <option value="popup_checkout" <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'popup_checkout'); ?>>Popup Checkout</option>
                                            <!-- <option disabled value="advanced" <?php //selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'advanced'); 
                                                                                    ?>>Advanced Checkout (Coming Soon)</option> -->
                                            <option value="side_cart" <?php selected(get_option('rmenupro_wc_checkout_method', 'direct_checkout'), 'side_cart'); ?>>Side Cart Slide-in</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Clear Cart Before Adding', 'When enabled, the cart will be emptied before adding the new product. This creates a single-product checkout experience.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_wc_clear_cart', 0); ?>
                                    </td>
                                </tr>
                                <!-- <div class="rmenupro-settings-row <?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                                        ?>">
                        <div class="rmenupro-settings-field">
                            <label class="rmenupro-settings-label">Single Checkout without Clear Cart (Coming Soon)</label>
                            <div class="rmenupro-settings-control">
                                <label class="rmenupro-toggle-switch">
                                    <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                            ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_wc_single_checkout' : 'rmenupro_wc_single_checkout'; 
                                                                        ?>" value="1" <?php //checked(1, get_option("rmenupro_wc_single_checkout", 0), true); 
                                                                                        ?> />
                                    <span class="rmenupro-toggle-slider"></span>
                                </label>
                                <p class="rmenupro-field-description">When enabled, the cart will not be emptied before adding the new product.</p>
                            </div>
                        </div>
                    </div> -->

                                <!-- <div class="rmenupro-settings-row <?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                                        ?>">
                        <div class="rmenupro-settings-field">
                            <label class="rmenupro-settings-label">One-Click Purchase (Coming Soon)</label>
                            <div class="rmenupro-settings-control">
                                <label class="rmenupro-toggle-switch">
                                    <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                            ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_wc_one_click_purchase' : 'rmenupro_wc_one_click_purchase'; 
                                                                        ?>" value="1" <?php //checked(1, get_option("rmenupro_wc_one_click_purchase", 0), true); 
                                                                                        ?> />
                                    <span class="rmenupro-toggle-slider"></span>
                                </label>
                                <p class="rmenupro-field-description">When enabled, returning customers can bypass the checkout form and use their last saved payment method. Requires WooCommerce Payments or compatible gateway.</p>
                            </div>
                        </div>
                    </div> -->

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show Confirmation Dialog', 'When enabled, customers will see a confirmation dialog before proceeding to checkout.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_wc_add_confirmation', 0); ?>
                                    </td>
                                </tr>
                            </table>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const checkoutMethodSelect = document.getElementById('rmenupro-checkout-method');
                                    const stickyCartCheckbox = document.querySelector('input[name="rmenu_enable_sticky_cart"]');
                                    const sideCartOption = checkoutMethodSelect.querySelector('option[value="side_cart"]');

                                    function updateSideCartOption() {
                                        if (!stickyCartCheckbox.checked) {
                                            sideCartOption.disabled = true;
                                            if (checkoutMethodSelect.value === 'side_cart') {
                                                showDirectCheckoutWarning(
                                                    checkoutMethodSelect.closest('tr'),
                                                    'Enable Sticky Cart in Floating Cart settings to use Side Cart Slide-in or use others Checkout Method.'
                                                );
                                            }
                                        } else {
                                            sideCartOption.disabled = false;
                                            removeDirectCheckoutWarning(checkoutMethodSelect.closest('tr'));
                                        }
                                    }

                                    stickyCartCheckbox.addEventListener('change', updateSideCartOption);
                                    checkoutMethodSelect.addEventListener('change', updateSideCartOption);

                                    // Initial check
                                    updateSideCartOption();
                                });
                            </script>

                        </div>

                        <div class="rmenupro-settings-section direct-button-style-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <th class="rmenu-settings-label">Button Style</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_wc_checkout_style" class="rmenupro-select" id="rmenupro-style-select">
                                                <option value="default" <?php selected(get_option('rmenupro_wc_checkout_style', 'alt'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option value="alt" <?php selected(get_option('rmenupro_wc_checkout_style', 'alt'), 'alt'); ?>>Alternative Style</option>
                                                <option value="custom" <?php selected(get_option('rmenupro_wc_checkout_style', 'alt'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Button Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenupro_wc_checkout_color" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_color', '#000')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Text Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_wc_checkout_text_color" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_text_color', '#ffffff')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Hover Background Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_wc_checkout_hover_bg_color" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_hover_bg_color', '#222222')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Hover Text Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_wc_checkout_hover_text_color" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_hover_text_color', '#ffffff')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Border Radius</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_wc_checkout_border_radius" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_border_radius', '4')); ?>" class="small-text" min="0" max="50" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Button Font Size</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_wc_checkout_font_size" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_font_size', '14')); ?>" class="small-text" min="10" max="30" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Button Width</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_wc_checkout_width" class="rmenupro-select">
                                                <option value="auto" <?php selected(get_option('rmenupro_wc_checkout_width', 'auto'), 'auto'); ?>>Auto</option>
                                                <option value="full" <?php selected(get_option('rmenupro_wc_checkout_width', 'auto'), 'full'); ?>>Full Width</option>
                                                <option value="custom" <?php selected(get_option('rmenupro_wc_checkout_width', 'auto'), 'custom'); ?>>Custom Width</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="rmenupro-checkout-custom-width-row" style="<?php echo (get_option('rmenupro_wc_checkout_width', 'auto') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <th class="rmenupro-settings-label">Custom Width Value</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_wc_checkout_custom_width" value="<?php echo esc_attr(get_option('rmenupro_wc_checkout_custom_width', '220')); ?>" class="small-text" min="50" max="500" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>


                                    <tr class="rmenupro-settings-row rmenupro-custom-css-row" id="rmenupro-custom-css-row" style="<?php echo (get_option('rmenupro_wc_checkout_style', 'default') == 'custom') ? 'display:flex;' : 'display:none;'; ?> grid-row: span 2;">
                                        <th class="rmenupro-settings-label">Custom CSS</th>
                                        <td class="rmenupro-settings-control" style="max-width: 100% !important;">
                                            <textarea name="rmenupro_wc_checkout_custom_css" class="rmenupro-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenupro_wc_checkout_custom_css', '')); ?></textarea>
                                            <p class="rmenupro-field-description">Add custom CSS for advanced button styling. Use the class <code>.opqcfw-btn</code> to target the button.</p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Button Icon</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_wc_checkout_icon" class="rmenupro-select">
                                                <option value="none" <?php selected(get_option('rmenupro_wc_checkout_icon', 'cart'), 'none'); ?>>No Icon</option>
                                                <option value="cart" <?php selected(get_option('rmenupro_wc_checkout_icon', 'cart'), 'cart'); ?>>Cart Icon</option>
                                                <option value="checkout" <?php selected(get_option('rmenupro_wc_checkout_icon', 'cart'), 'checkout'); ?>>Checkout Icon</option>
                                                <option value="arrow" <?php selected(get_option('rmenupro_wc_checkout_icon', 'cart'), 'arrow'); ?>>Arrow Icon</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Icon Position</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_wc_checkout_icon_position" class="rmenupro-select">
                                                <option value="left" <?php selected(get_option('rmenupro_wc_checkout_icon_position', 'left'), 'left'); ?>>Left</option>
                                                <option value="right" <?php selected(get_option('rmenupro_wc_checkout_icon_position', 'left'), 'right'); ?>>Right</option>
                                                <option value="top" <?php selected(get_option('rmenupro_wc_checkout_icon_position', 'left'), 'top'); ?>>Top</option>
                                                <option value="bottom" <?php selected(get_option('rmenupro_wc_checkout_icon_position', 'left'), 'bottom'); ?>>Bottom</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-4 select[name="rmenupro_wc_checkout_style"]');

                            const buttonStyleSection = document.querySelector('.rmenupro-settings-section.direct-button-style-section  table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);
                            const checkoutWidthField = document.querySelector('select[name="rmenupro_wc_checkout_width"]');
                            const checkoutCustomWidthRow = document.getElementById('rmenupro-checkout-custom-width-row');

                            function toggleCheckoutCustomWidthRow() {
                                if (!checkoutWidthField || !checkoutCustomWidthRow) {
                                    return;
                                }

                                const isCustomWidth = checkoutWidthField.value === 'custom';
                                const hasCustomStyleEnabled = button_style && button_style.value !== 'default';
                                checkoutCustomWidthRow.style.display = (hasCustomStyleEnabled && isCustomWidth) ? 'flex' : 'none';
                            }

                            // if rmenupro_wc_checkout_icon is 'none', disable the icon position field
                            const iconSelect = document.querySelector('select[name="rmenupro_wc_checkout_icon"]');
                            const iconPositionField = document.querySelector('select[name="rmenupro_wc_checkout_icon_position"]');
                            if (iconSelect && iconPositionField) {
                                iconSelect.addEventListener('change', function() {
                                    toggleDisabledClass(this.value === 'none', iconPositionField);
                                });

                                // Trigger change event on page load to set initial visibility
                                setTimeout(() => {
                                    iconSelect.dispatchEvent(new Event('change'));
                                }, 1000);
                            }

                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                        document.querySelector('#rmenupro-custom-css-row').style.display = (this.value === 'custom') ? 'flex' : 'none';
                                        toggleCheckoutCustomWidthRow();
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                        if (checkoutCustomWidthRow) {
                                            checkoutCustomWidthRow.style.display = 'none';
                                        }
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            if (checkoutWidthField) {
                                checkoutWidthField.addEventListener('change', toggleCheckoutCustomWidthRow);
                                checkoutWidthField.dispatchEvent(new Event('change'));
                            }

                            // if rmenupro_wc_checkout_color (which is bg color) & rmenupro_wc_checkout_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenupro_wc_checkout_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenupro_wc_checkout_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }

                            // if rmenupro_wc_checkout_hover_bg_color (which is bg color) & rmenupro_wc_checkout_hover_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutHoverColor = document.querySelector('input[name="rmenupro_wc_checkout_hover_bg_color"]');
                            const checkoutHoverTextColor = document.querySelector('input[name="rmenupro_wc_checkout_hover_text_color"]');
                            if (checkoutHoverColor && checkoutHoverTextColor) {
                                checkoutHoverColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutHoverTextColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                            }

                        });
                    </script>

                </div>



                <div class="tab-content" id="direct-advanced" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-category"></span>', 'Checkout in Variable Product'); ?>
                            <table class="form-table plugincy_table variable-product-table">

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Show Variation Selection in Archive pages', 'When enabled, the variation selection will be shown on archive pages.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_variation_show_archive', 1); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Variation Selection Layout', 'Choose variation selection Layout.'); ?>
                                    <td>
                                        <div class="rmenu-settings-control">
                                            <select name="rmenu_variation_layout" class="rmenu-select">
                                                <option value="combine" <?php selected(get_option('rmenu_variation_layout', 'separate'), 'combine'); ?>>Combine</option>
                                                <option value="separate" <?php selected(get_option('rmenu_variation_layout', 'separate'), 'separate'); ?>>Separate</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Show Variation Title', 'When enabled, the variation title will be shown on archive pages.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_show_variation_title', 0); ?>
                                    </td>
                                </tr>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const variationLayoutSelect = document.querySelector('select[name="rmenu_variation_layout"]');
                                        const variationTitleSwitch = document.querySelector('input[name="rmenu_show_variation_title"]');

                                        // Initial check
                                        setTimeout(() => {
                                            toggleDisabledClass(variationLayoutSelect.value === 'combine', variationTitleSwitch);
                                            variationLayoutSelect.dispatchEvent(new Event('change'));
                                        }, 1000);


                                        // Event listener for dropdown change
                                        variationLayoutSelect.addEventListener('change', function() {
                                            toggleDisabledClass(this.value === 'combine', variationTitleSwitch);
                                        });
                                    });
                                </script>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Hide Select Option Button', 'When enabled, the select option button will be hidden on variable product pages.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_wc_hide_select_option', 0); ?>
                                    </td>
                                </tr>
                            </table>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                                    const enableCheckout = document.querySelector('div#tab-4 input[name="rmenupro_variation_show_archive"]');

                                    const allinputFields = Array.from(document.querySelectorAll('div#tab-4 table.variable-product-table input, div#tab-4 table.variable-product-table select')).filter(
                                        el => !(el.name === "rmenupro_variation_show_archive")
                                    ).filter(
                                        el => !(el.name === "rmenupro_wc_hide_select_option")
                                    );
                                    toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                                    enableCheckout.addEventListener('change', function() {
                                        toggleDisabledClass(!this.checked, allinputFields);
                                    });
                                });
                            </script>
                        </div>

                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-tools"></span>', 'Advanced Options'); ?>

                            <!-- <div class="rmenupro-settings-row">
                            <div class="rmenupro-settings-field">
                                <label class="rmenupro-settings-label">Mobile Optimization</label>
                                <div class="rmenupro-settings-control">
                                    <label class="rmenupro-toggle-switch">
                                        <input type="checkbox" name="rmenupro_wc_checkout_mobile_optimize" value="1" <?php //checked(1, get_option("rmenupro_wc_checkout_mobile_optimize", 1), true); 
                                                                                                                        ?> />
                                        <span class="rmenupro-toggle-slider"></span>
                                    </label>
                                    <p class="rmenupro-field-description">When enabled, the direct checkout button will be optimized for mobile devices.</p>
                                </div>
                            </div>
                        </div> -->

                            <table class="form-table plugincy_table">

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable for Guest Checkout', 'When enabled, the direct checkout button will be available for guest users. When disabled, only logged-in users will see the button.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_wc_checkout_guest_enabled', 1); ?>
                                    </td>
                                </tr>
                            </table>

                        </div>
                    </div>

                    <style>
                        .rmenupro-settings-header {
                            margin-bottom: 30px;
                        }

                        .rmenupro-settings-header h2 {
                            font-size: 24px;
                            font-weight: 600;
                            margin: 0 0 10px 0;
                            padding: 0;
                        }

                        .rmenupro-settings-description {
                            font-size: 14px;
                            color: #646970;
                            margin: 0;
                            padding: 0;
                        }

                        .rmenupro-settings-section {
                            position: relative;
                        }

                        .rmenupro-settings-section-header {
                            border-bottom: 1px solid #c3c4c7;
                            padding: 12px 15px;
                            background: #f0f0f1;
                        }

                        .rmenupro-settings-section-header h3 {
                            margin: 0;
                            font-size: 14px;
                            font-weight: 600;
                            line-height: 1.4;
                        }

                        .rmenupro-settings-section-header h3 .dashicons {
                            font-size: 16px;
                            height: 16px;
                            width: 16px;
                            margin-right: 6px;
                            color: #646970;
                        }

                        .rmenupro-settings-row {
                            padding: 15px;
                            border-bottom: 1px solid #f0f0f1;
                            position: relative;
                            display: flex;
                            flex-wrap: wrap;
                        }

                        .rmenupro-settings-row:last-child {
                            border-bottom: none;
                        }

                        .rmenupro-settings-row-columns {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 20px;
                        }

                        .rmenupro-settings-column {
                            flex: 1;
                            min-width: 200px;
                        }

                        .rmenupro-settings-label {
                            font-weight: 600;
                            margin-bottom: 8px;
                            font-size: 14px;
                        }

                        .rmenupro-settings-control {
                            flex: 1;
                        }

                        .rmenupro-field-description {
                            color: #646970;
                            font-size: 13px;
                            margin: 5px 0 0;
                            font-style: italic;
                        }

                        .rmenupro-checkbox-group {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 20px;
                        }

                        .rmenupro-checkbox-column {
                            flex: 1;
                            min-width: 200px;
                        }

                        .rmenupro-checkbox-column h4 {
                            margin: 0 0 10px 0;
                            padding: 0;
                            font-size: 14px;
                            color: #3c434a;
                        }

                        .rmenupro-checkbox-container {
                            display: block;
                            position: relative;
                            padding: 5px 0;
                            cursor: pointer;
                            user-select: none;
                        }

                        .rmenupro-checkbox-label {
                            margin-left: 5px;
                            font-size: 13px;
                        }

                        .rmenupro-toggle-switch {
                            position: relative;
                            display: inline-block;
                            width: 40px;
                            height: 22px;
                        }

                        .rmenupro-toggle-switch input {
                            opacity: 0;
                            width: 0;
                            height: 0;
                        }

                        .rmenupro-toggle-slider {
                            position: absolute;
                            cursor: pointer;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background-color: #ccc;
                            transition: .4s;
                            border-radius: 34px;
                        }

                        .rmenupro-toggle-slider:before {
                            position: absolute;
                            content: "";
                            height: 18px;
                            width: 18px;
                            left: 2px;
                            bottom: 2px;
                            background-color: white;
                            transition: .4s;
                            border-radius: 50%;
                        }

                        input:checked+.rmenupro-toggle-slider {
                            background-color: #2271b1;
                        }

                        input:focus+.rmenupro-toggle-slider {
                            box-shadow: 0 0 1px #2271b1;
                        }

                        input:checked+.rmenupro-toggle-slider:before {
                            transform: translateX(18px);
                        }

                        .rmenupro-color-picker {
                            padding: 0;
                            border: 1px solid #8c8f94;
                            border-radius: 4px;
                            height: 30px;
                            width: 60px;
                        }

                        .rmenupro-textarea-code {
                            min-height: 100px;
                            width: 100%;
                            font-family: monospace;
                        }
                    </style>

                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            $('#rmenupro-style-select').on('change', function() {
                                if ($(this).val() === 'custom') {
                                    $('#rmenupro-custom-css-row').css('display', 'flex');
                                } else {
                                    $('#rmenupro-custom-css-row').hide();
                                }
                            });
                        });
                    </script>
                    <script>
                        function showDirectCheckoutWarning(highlightSection, message) {
                            let popup = document.getElementById('rmenupro-enable-atc-popup');
                            if (highlightSection) {
                                highlightSection.style.border = '2px solid #dc3545';
                                highlightSection.style.padding = '10px';
                            }
                            if (!popup) {
                                popup = document.createElement('div');
                                popup.id = 'rmenupro-enable-atc-popup';
                                popup.innerHTML = `
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                        background: #dc3545;
                                        color: #fff;
                                        padding: 16px 28px 16px 16px;
                                        border-radius: 6px;
                                        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                                        font-size: 15px;
                                        position: fixed;
                                        top: 30px;
                                        right: 30px;
                                        z-index: 99999;
                                    ">
                                        <span style="font-size:18px;vertical-align:middle;" class="dashicons dashicons-warning"></span>
                                        <span>
                                            <b>${message}</b>
                                        </span>
                                        <span id="rmenupro-enable-atc-popup-close" style="margin-left:12px;cursor:pointer;font-size:18px;">&times;</span>
                                    </div>
                                `;
                                document.body.appendChild(popup);
                                document.getElementById('rmenupro-enable-atc-popup-close').onclick = function() {
                                    popup.remove();
                                };
                                // Only remove the popup if it's not being hovered
                                let isHovered = false;
                                popup.addEventListener('mouseenter', function() {
                                    isHovered = true;
                                });
                                popup.addEventListener('mouseleave', function() {
                                    isHovered = false;
                                });
                                setTimeout(function() {
                                    if (popup && !isHovered) popup.remove();
                                }, 3500);
                            }
                        }
                        // Reusable function to remove warning popup and highlight
                        function removeDirectCheckoutWarning(highlightSection) {
                            if (highlightSection) {
                                highlightSection.style.border = '';
                                highlightSection.style.padding = '';
                            }
                            const existingPopup = document.getElementById('rmenupro-enable-atc-popup');
                            if (existingPopup) {
                                existingPopup.remove();
                            }
                        }
                        document.addEventListener('DOMContentLoaded', function() {
                            // Tab click handler for direct checkout settings tabs
                            const tabItems = document.querySelectorAll('#tab-4 .rmenupro-settings-tab-item');
                            const tabContents = document.querySelectorAll('#tab-4 .tab-content');
                            const enableDirectCheckout = document.querySelector('input[name="rmenupro_add_direct_checkout_button"]');
                            const typeCheckboxes = document.querySelectorAll('input[name="rmenupro_show_quick_checkout_by_types[]"]');
                            const highlightSection = document.getElementById('rmenupro-direct-button-display-section');
                            const highlight_enableSection = document.getElementById('rmenupro-direct-checkout-enable-field');
                            // rmenupro_show_quick_checkout_by_page[]
                            const pageCheckboxes = document.querySelectorAll('input[name="rmenupro_show_quick_checkout_by_page[]"]');

                            enableDirectCheckout.addEventListener('change', function() {
                                if (enableDirectCheckout.checked) {
                                    removeDirectCheckoutWarning(highlight_enableSection);
                                    const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                    const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                    if (allTypeUnchecked || allPageUnchecked) {
                                        showDirectCheckoutWarning(
                                            highlightSection,
                                            'Please select at least one product type and one page to show changes.'
                                        );
                                    }
                                }
                            });

                            // On change pageCheckboxes & typeCheckboxes & if at least one of them is checked, remove highlight and popup
                            function handleDirectCheckoutCheckboxChange() {
                                const allChecked = Array.from(typeCheckboxes).some(checkbox => checkbox.checked);
                                const allPageChecked = Array.from(pageCheckboxes).some(checkbox => checkbox.checked);
                                if (allChecked && allPageChecked) {
                                    removeDirectCheckoutWarning(highlightSection);
                                } else {
                                    if (enableDirectCheckout.checked) {
                                        const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                        const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                        if (allTypeUnchecked || allPageUnchecked) {
                                            showDirectCheckoutWarning(
                                                highlightSection,
                                                'Please select at least one product type and one page to show changes.'
                                            );
                                        }
                                    }
                                }
                            }
                            typeCheckboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', handleDirectCheckoutCheckboxChange);
                            });
                            pageCheckboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', handleDirectCheckoutCheckboxChange);
                            });

                            tabItems.forEach(function(tab) {
                                tab.addEventListener('click', function() {
                                    // Remove active class from all tabs
                                    tabItems.forEach(function(t) {
                                        t.classList.remove('active');
                                    });
                                    // Hide all tab contents
                                    tabContents.forEach(function(content) {
                                        content.style.display = 'none';
                                    });

                                    // Add active class to clicked tab
                                    tab.classList.add('active');
                                    // Show the corresponding tab content
                                    const tabId = tab.getAttribute('data-tab');

                                    if (tabId !== "direct-general-settings" && enableDirectCheckout && !enableDirectCheckout.checked) {

                                        showDirectCheckoutWarning(
                                            highlight_enableSection,
                                            '<b>Enable Direct Checkout</b> in the general settings tab to access these options.'
                                        );
                                    } else {
                                        // Hide any existing popup
                                        const existingPopup = document.getElementById('rmenupro-enable-atc-popup');
                                        if (existingPopup) {
                                            existingPopup.remove();
                                        }
                                    }
                                    if (enableDirectCheckout.checked) {
                                        const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                        const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                        if (allTypeUnchecked || allPageUnchecked) {
                                            showDirectCheckoutWarning(
                                                highlightSection,
                                                'Please select at least one product type and one page to show changes.'
                                            );
                                        }
                                    }
                                    const content = document.getElementById(tabId);
                                    if (content) {
                                        content.style.display = 'block';
                                    }
                                });
                            });

                            // Show only the first tab content by default
                            tabContents.forEach(function(content, idx) {
                                content.style.display = (idx === 0) ? 'block' : 'none';
                            });
                        });
                    </script>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                        const enableCheckout = document.querySelector('div#tab-4 input[name="rmenupro_add_direct_checkout_button"]');

                        const allinputFields = Array.from(document.querySelectorAll('div#tab-4 input, div#tab-4 select')).filter(
                            el => !(el.name === "rmenupro_add_direct_checkout_button")
                        );
                        toggleDisabledClass(!enableCheckout.checked, allinputFields);
                        enableCheckout.addEventListener('change', function() {
                            toggleDisabledClass(!this.checked, allinputFields);
                        });
                    });
                </script>
            </div>
            <div class="tab-content plugincy_card" id="tab-5" style="margin-top: 20px;">
                <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-star-filled"></span>', 'Manage Features', 'Enhance your WooCommerce checkout experience with these powerful features.'); ?>
                <table class="form-table plugincy_table">
                    <tbody style="display: grid; grid-template-columns: repeat(auto-fill, minmax(410px, 1fr));">
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Product Quantity Controller', 'Enable "Product Quantity Controller" to manage product quantities in the checkout form.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenupro_quantity_control', 1); ?>
                            </td>
                        </tr>
                        <tr valign="top" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Remove Product Button', 'Enable "Remove Product Button" to allow customers to remove products from the checkout form.'); ?>
                            <td>
                                <label class="switch">
                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_remove_product' : 'rmenupro_remove_product'; ?>" value="1" <?php checked(1, get_option("rmenupro_remove_product", "1"), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr valign="top" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Variation Switcher in Cart & Checkout', 'Enable this Pro feature to let customers switch variable-product options directly from the cart drawer, cart page, and checkout instead of adding a new variation and removing the old one manually.'); ?>
                            <td>
                                <label class="switch">
                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_cart_checkout_variation_switch' : 'rmenupro_cart_checkout_variation_switch'; ?>" value="1" <?php checked(1, get_option("rmenupro_cart_checkout_variation_switch", 0), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Add Image Before Product', 'Enable "Add Image Before Product" to display product images before their titles in the checkout form.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenupro_add_img_before_product', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'At Least One Product in Cart', 'Enable "At Least One Product in Cart" to add at least one product in the cart.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenupro_at_one_product_cart', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Disable Cart Page', 'Enable "Disable Cart Page" to remove the cart page from your WooCommerce store. This will redirect customers directly to the checkout page.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenupro_disable_cart_page', 0); ?>
                            </td>
                        </tr>
                        <!-- <tr valign="top" class="<?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                        ?>">
                        <th scope="row">Express Checkout Options (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                        ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_express_checkout' : 'rmenupro_express_checkout'; 
                                                                    ?>" value="1" <?php //checked(1, get_option("rmenupro_express_checkout", 0), true); 
                                                                                    ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Express Checkout Options" to allow customers to use Express Checkout Options like PayPal, Stripe, etc.</span>
                            </span>
                        </td>
                    </tr> -->
                        <!-- <tr valign="top" class="<?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                        ?>">
                        <th scope="row">Address Auto-Complete (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                        ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_address_auto_complete' : 'rmenupro_address_auto_complete'; 
                                                                    ?>" value="1" <?php //checked(1, get_option("rmenupro_address_auto_complete", 0), true); 
                                                                                    ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Address Auto-Complete" to automatically fill in address fields based on user input.</span>
                            </span>
                        </td>
                    </tr> -->
                        <!-- <tr valign="top" class="<?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                        ?>">
                        <th scope="row">Multi-Step Checkout (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                        ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_multi_step_checkout' : 'rmenupro_multi_step_checkout'; 
                                                                    ?>" value="1" <?php //checked(1, get_option("rmenupro_multi_step_checkout", 0), true); 
                                                                                    ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Multi-step checkout" to break the checkout process into multiple steps for better user experience.</span>
                            </span>
                        </td>
                    </tr> -->
                        <tr valign="top" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Force Login Before Checkout', 'Enable "Force Login Before Checkout" to require customers to log in before they can access the checkout page.'); ?>
                            <td>
                                <label class="switch">
                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_force_login' : 'rmenupro_force_login'; ?>" value="1" <?php checked(1, get_option("rmenupro_force_login", 0), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Link Product Name in Checkout Page', 'Enable "Link Product Name in Checkout Page" to make the product names clickable, redirecting customers to the product page.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenupro_link_product', 1); ?>
                            </td>
                        </tr>
                        <!-- <tr valign="top" class="<?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                        ?>">
                        <th scope="row">Enable Captcha on Checkout Page (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                        ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_enable_captcha' : 'rmenupro_enable_captcha'; 
                                                                    ?>" value="1" <?php //checked(1, get_option("rmenupro_enable_captcha", 0), true); 
                                                                                    ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Captcha on checkout page" to add a captcha verification step to the checkout process, enhancing security against spam and bots.</span>
                            </span>
                        </td>
                    </tr> -->
                    </tbody>
                </table>
            </div>
            <div class="tab-content plugincy_card" id="tab-6" style="margin-top: 20px;">
                <?php onepaqucpro_trust_badges_settings_content(); ?>
                <table style="padding-top: 1rem;">
                    <tr valign="top" style="display: flex; gap: 51px;">
                        <?php $onepaquc_helper->sec_head('th', '', '', 'Contribute to Plugincy', 'We collect non-sensitive technical details from your website, like the PHP version and features usage, to help us troubleshoot issues faster, make informed development decisions, and build features that truly benefit you. <a href="https://plugincy.com/usage-tracking/" target="_blank" style="color:#fff;">Learn more…</a>'); ?>
                        <td>
                            <?php $onepaquc_helper->switcher('rmenupro_allow_analytics', 1); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-7">
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-visibility"></span>', 'WooCommerce Quick View', '', 'Manage the quick view settings for your WooCommerce products.'); ?>
                    <div class="rmenupro-settings-tabs">
                        <ul class="rmenupro-settings-tab-list">
                            <li class="rmenupro-settings-tab-item active" data-tab="quick-general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenupro-settings-tab-item" data-tab="quick-popup">
                                <span class="dashicons dashicons-admin-comments"></span> Popup Manage
                            </li>
                            <li class="rmenupro-settings-tab-item" data-tab="quick-advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content" id="quick-general-settings" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>
                            <table class="form-table plugincy_table">
                                <tr class="rmenupro-settings-field">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Quick View', 'Enable or disable the quick view functionality across your WooCommerce store.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_enable_quick_view', 0); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Text', 'Customize the text displayed on the quick view button.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php
                                        $quick_view_button_text = get_option('rmenupro_quick_view_button_text', '');
                                        if (empty($quick_view_button_text)) {
                                            $quick_view_button_text = 'Quick View';
                                        }
                                        ?>
                                        <input type="text" name="rmenupro_quick_view_button_text" value="<?php echo esc_attr($quick_view_button_text); ?>"  />
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position', 'Choose where to display the quick view button on product listings.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <select name="rmenupro_quick_view_button_position" class="rmenupro-select">
                                            <option value="after_image" <?php selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'after_image'); ?>>After Product Image</option>
                                            <!-- <option value="before_title" <?php //selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'before_title'); 
                                                                                ?>>Before Product Title (Coming Soon)</option>
                                        <option value="after_title" <?php //selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'after_title'); 
                                                                    ?>>After Product Title (Coming Soon)</option>
                                        <option value="before_price" <?php //selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'before_price'); 
                                                                        ?>>Before Product Price (Coming Soon)</option>
                                        <option value="after_price" <?php //selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'after_price'); 
                                                                    ?>>After Product Price (Coming Soon)</option>
                                        <option value="before_add_to_cart" <?php //selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'before_add_to_cart'); 
                                                                            ?>>Before Add to Cart Button (Coming Soon)</option> -->
                                            <option value="after_add_to_cart" <?php selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                            <option value="image_overlay" <?php selected(get_option('rmenupro_quick_view_button_position', 'image_overlay'), 'image_overlay'); ?>>Overlay on Product Image</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Display Type', 'Choose how the quick view trigger should appear to customers.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <select name="rmenupro_quick_view_display_type" class="rmenupro-select">
                                            <option value="button" <?php selected(get_option('rmenupro_quick_view_display_type', 'icon'), 'button'); ?>>Button</option>
                                            <option value="icon" <?php selected(get_option('rmenupro_quick_view_display_type', 'icon'), 'icon'); ?>>Icon Only</option>
                                            <option value="text_icon" <?php selected(get_option('rmenupro_quick_view_display_type', 'icon'), 'text_icon'); ?>>Text with Icon</option>
                                            <!-- <option value="hover_icon" <?php //selected(get_option('rmenupro_quick_view_display_type', 'icon'), 'hover_icon'); 
                                                                            ?>>Hover Icon</option> -->
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="rmenupro-settings-section quick-view-button-style plugincy_card plugincy_col-5" id="rmenupro-quick-view-button-style-section">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Style', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_quick_view_button_style" class="rmenupro-select" id="rmenupro-qv-style-select">
                                                <option value="default" <?php selected(get_option('rmenupro_quick_view_button_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option value="alt" <?php selected(get_option('rmenupro_quick_view_button_style', 'default'), 'alt'); ?>>Alternative Style</option>
                                                <option value="custom" <?php selected(get_option('rmenupro_quick_view_button_style', 'default'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Color', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_quick_view_button_color" value="<?php echo esc_attr(get_option('rmenupro_quick_view_button_color', '#000')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Text Color', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_quick_view_text_color" value="<?php echo esc_attr(get_option('rmenupro_quick_view_text_color', '#ffffff')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Icon', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_quick_view_button_icon" class="rmenupro-select">
                                                <option value="none" <?php selected(get_option('rmenupro_quick_view_button_icon', 'eye'), 'none'); ?>>No Icon</option>
                                                <option value="eye" <?php selected(get_option('rmenupro_quick_view_button_icon', 'eye'), 'eye'); ?>>Eye Icon</option>
                                                <option value="search" <?php selected(get_option('rmenupro_quick_view_button_icon', 'eye'), 'search'); ?>>Search Icon</option>
                                                <option value="zoom" <?php selected(get_option('rmenupro_quick_view_button_icon', 'eye'), 'zoom'); ?>>Zoom Icon</option>
                                                <option value="preview" <?php selected(get_option('rmenupro_quick_view_button_icon', 'eye'), 'preview'); ?>>Preview Icon</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Icon Position', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_quick_view_icon_position" class="rmenupro-select">
                                                <option value="left" <?php selected(get_option('rmenupro_quick_view_icon_position', 'left'), 'left'); ?>>Left</option>
                                                <option value="right" <?php selected(get_option('rmenupro_quick_view_icon_position', 'left'), 'right'); ?>>Right</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="rmenupro-qv-custom-css-row" style="<?php echo (get_option('rmenupro_quick_view_button_style', 'default') == 'custom') ? 'display:flex;' : 'display:none;'; ?> flex-direction: column;align-items: flex-start;">
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Custom CSS', ''); ?>
                                        <td class="rmenupro-settings-control" style="max-width: 100% !important;">
                                            <textarea name="rmenupro_quick_view_custom_css" class="rmenupro-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenupro_quick_view_custom_css', '')); ?></textarea>
                                            <p class="rmenupro-field-description">Add custom CSS for advanced button styling. Use the class <code>.opqvfw-btn</code> to target the button and <code>.opqvfw-modal</code> to target the modal.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-7 select[name="rmenupro_quick_view_button_style"]');

                            const buttonStyleSection = document.querySelector('.rmenupro-settings-section.quick-view-button-style table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);

                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            // if rmenupro_quick_view_button_color (which is bg color) & rmenupro_quick_view_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenupro_quick_view_button_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenupro_quick_view_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }
                        });
                    </script>
                </div>
                <div class="tab-content" id="quick-popup" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Quick View Content', ''); ?>
                            <div class="rmenupro-settings-row">
                                <div class="rmenupro-settings-field" id="rmenupro-quick-view-content-elements">
                                    <label class="rmenupro-settings-label">Content Elements</label>
                                    <?php $content_elements_option = get_option('rmenupro_quick_view_content_elements', ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta']); ?>
                                    <div class="rmenupro-settings-control rmenupro-checkbox-group">
                                        <div class="rmenupro-checkbox-column">
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="image" <?php checked(in_array('image', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Image</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="gallery" <?php checked(in_array('gallery', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Gallery</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="title" <?php checked(in_array('title', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Title</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="rating" <?php checked(in_array('rating', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Rating</span>
                                            </label>
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="price" <?php checked(in_array('price', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Price</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="excerpt" <?php checked(in_array('excerpt', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Short Description</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="add_to_cart" <?php checked(in_array('add_to_cart', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Add to Cart Button</span>
                                            </label>

                                            <!-- <label class="rmenupro-checkbox-container">
                                        <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="quantity" <?php //checked(in_array('quantity', $content_elements_option)); 
                                                                                                                                ?> />
                                        <span class="rmenupro-checkbox-label">Quantity Selector</span>
                                    </label> -->
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_quick_view_content_elements[]" value="meta" <?php checked(in_array('meta', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Meta</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                                <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_content_elements[]' : 'rmenupro_quick_view_content_elements[]'; ?>" value="sharing" <?php checked(in_array('sharing', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Social Sharing</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" id="view_details_checkbox" name="rmenupro_quick_view_content_elements[]" value="view_details" <?php checked(in_array('view_details', $content_elements_option)); ?> />
                                                <span class="rmenupro-checkbox-label">View Details Link</span>
                                            </label>

                                            <!-- <label class="rmenupro-checkbox-container <?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                                                            ?>">
                                        <input <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                                ?> type="checkbox" name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_quick_view_content_elements[]' : 'rmenupro_quick_view_content_elements[]'; 
                                                                            ?>" value="attributes" <?php //checked(in_array('attributes', $content_elements_option)); 
                                                                                                    ?> />
                                        <span class="rmenupro-checkbox-label">Product Attributes (Coming Soon)</span>
                                    </label> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Modal Size', 'Choose the size of the quick view modal popup.'); ?>
                                        <td class="rmenupro-settings-control">
                                            <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_modal_size' : 'rmenupro_quick_view_modal_size'; ?>" class="rmenupro-select">
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="small" <?php selected(get_option('rmenupro_quick_view_modal_size', 'medium'), 'small'); ?>>Small</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="medium" <?php selected(get_option('rmenupro_quick_view_modal_size', 'medium'), 'medium'); ?>>Medium</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="large" <?php selected(get_option('rmenupro_quick_view_modal_size', 'medium'), 'large'); ?>>Large</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="full" <?php selected(get_option('rmenupro_quick_view_modal_size', 'medium'), 'full'); ?>>Full Width</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="custom" <?php selected(get_option('rmenupro_quick_view_modal_size', 'medium'), 'custom'); ?>>Custom</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?> rmenupro-custom-size-row" style="<?php echo (get_option('rmenupro_quick_view_modal_size', 'medium') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Custom Width', 'Custom width in pixels (e.g., 800).'); ?>
                                        <td class="rmenupro-settings-control">
                                            <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_custom_width' : 'rmenupro_quick_view_custom_width'; ?>" value="<?php echo esc_attr(get_option('rmenupro_quick_view_custom_width', '800')); ?>"  />
                                        </td>
                                    </tr>
                                    <tr class="rmenupro-custom-size-row rmenupro-settings-column <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>" style="<?php echo (get_option('rmenupro_quick_view_modal_size', 'medium') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Custom Height', 'Custom height in pixels (e.g., 600) or \'auto\'.'); ?>
                                        <td class="rmenupro-settings-control">
                                            <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_custom_height' : 'rmenupro_quick_view_custom_height'; ?>" value="<?php echo esc_attr(get_option('rmenupro_quick_view_custom_height', '600')); ?>"  />
                                        </td>
                                    </tr>

                                    <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Loading Effect', 'Choose the animation effect when opening the quick view modal.'); ?>
                                        <td class="rmenupro-settings-control">
                                            <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_loading_effect' : 'rmenupro_quick_view_loading_effect'; ?>" class="rmenupro-select">
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="fade" <?php selected(get_option('rmenupro_quick_view_loading_effect', 'fade'), 'fade'); ?>>Fade</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="slide" <?php selected(get_option('rmenupro_quick_view_loading_effect', 'fade'), 'slide'); ?>>Slide</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="zoom" <?php selected(get_option('rmenupro_quick_view_loading_effect', 'fade'), 'zoom'); ?>>Zoom</option>
                                                <option <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> value="none" <?php selected(get_option('rmenupro_quick_view_loading_effect', 'fade'), 'none'); ?>>None</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5" id="rmenupro-quick-view-display-settings">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-layout"></span>', 'Display Settings', ''); ?>
                            <div class="rmenupro-settings-row">
                                <div class="rmenupro-settings-field">
                                    <label class="rmenupro-settings-label">Product Types</label>
                                    <?php $product_types_option = get_option('rmenupro_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"]); ?>
                                    <div class="rmenupro-settings-control rmenupro-checkbox-group">
                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_view_by_types[]" value="simple" <?php checked(in_array('simple', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">Simple Products</span>
                                        </label>

                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_view_by_types[]" value="variable" <?php checked(in_array('variable', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">Variable Products</span>
                                        </label>

                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_view_by_types[]" value="grouped" <?php checked(in_array('grouped', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">Grouped Products</span>
                                        </label>

                                        <label class="rmenupro-checkbox-container">
                                            <input type="checkbox" name="rmenupro_show_quick_view_by_types[]" value="external" <?php checked(in_array('external', $product_types_option)); ?> />
                                            <span class="rmenupro-checkbox-label">External/Affiliate Products</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="rmenupro-settings-row">
                                <div class="rmenupro-settings-field">
                                    <?php $product_pages_option = get_option('rmenupro_show_quick_view_by_page', ['shop-page', 'category-archives', 'tag-archives', 'brand-archives', 'attribute-archives', 'single-product', 'search', 'featured-products', 'on-sale', 'recent', 'widgets', 'shortcodes']); ?>
                                    <div class="rmenupro-settings-control rmenupro-checkbox-group">
                                        <div class="rmenupro-checkbox-column">
                                            <h4>Archive Pages</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="shop-page" <?php checked(in_array('shop-page', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Main Shop Page</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="category-archives" <?php checked(in_array('category-archives', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Category Archives</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="tag-archives" <?php checked(in_array('tag-archives', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Tag Archives</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="brand-archives" <?php checked(in_array('brand-archives', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Brand Archives</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="attribute-archives" <?php checked(in_array('attribute-archives', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Product Attribute Archives</span>
                                            </label>
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <h4>Other Pages</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="single-product" <?php checked(in_array('single-product', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Single Product Pages</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="search" <?php checked(in_array('search', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Search Results</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="featured-products" <?php checked(in_array('featured-products', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Featured Products</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="on-sale" <?php checked(in_array('on-sale', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">On-Sale Products</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="recent" <?php checked(in_array('recent', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Recent Products</span>
                                            </label>
                                        </div>

                                        <div class="rmenupro-checkbox-column">
                                            <h4>Widgets & Shortcodes</h4>
                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="widgets" <?php checked(in_array('widgets', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Widgets</span>
                                            </label>

                                            <label class="rmenupro-checkbox-container">
                                                <input type="checkbox" name="rmenupro_show_quick_view_by_page[]" value="shortcodes" <?php checked(in_array('shortcodes', $product_pages_option)); ?> />
                                                <span class="rmenupro-checkbox-label">Shortcodes</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rmenupro-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-translation"></span>', 'Translations', ''); ?>
                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', '"View Full Details" Button Text', 'Change the text shown on the "View Full Details" button inside the Quick View popup.'); ?>
                                <td class="rmenupro-settings-control">
                                    <?php
                                    $details_text = get_option('rmenupro_quick_view_details_text', '');
                                    if (empty($details_text)) {
                                        $details_text = 'View Full Details';
                                    }
                                    ?>
                                    <input type="text" id="view_details_text" name="rmenupro_quick_view_details_text" value="<?php echo esc_attr($details_text); ?>"  />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Select the checkbox and the text input
                            const enableCheckout = document.getElementById('view_details_checkbox');
                            const allInputFields = document.getElementById('view_details_text');

                            // Function to toggle the disabled class
                            function toggleDisabledClass(disable, inputField) {
                                inputField.disabled = disable; // Toggle the disabled property
                            }

                            // Set initial state
                            toggleDisabledClass(!enableCheckout.checked, allInputFields);

                            // Add event listener for checkbox change
                            enableCheckout.addEventListener('change', function() {
                                toggleDisabledClass(!this.checked, allInputFields);
                            });
                        });
                    </script>
                </div>

                <div class="tab-content" id="quick-advanced" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-tools"></span>', 'Advanced Options', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Optimization', 'When enabled, the quick view functionality will be optimized for mobile devices.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_quick_view_mobile_optimize', 0); ?>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Close on Add to Cart', 'When enabled, the quick view popup will automatically close after adding a product to cart.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_quick_view_close_on_add', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Keyboard Navigation', 'When enabled, customers can use keyboard arrows to navigate between products in quick view and ESC to close.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_quick_view_keyboard_nav', 1); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-analytics"></span>', 'Analytics Integration', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Track Quick View Events', 'Track when customers use quick view in Google Analytics or other analytics tools.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_quick_view_track_events', 0); ?>
                                    </td>
                                </tr>
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Event Category', 'The event category name used for analytics tracking.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_event_category' : 'rmenupro_quick_view_event_category'; ?>" value="<?php echo esc_attr(get_option('rmenupro_quick_view_event_category', 'one-page-quick-checkout-for-woocommerce-pro')); ?>"  />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Event Action', 'The event action name used for analytics tracking.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_event_action' : 'rmenupro_quick_view_event_action'; ?>" value="<?php echo esc_attr(get_option('rmenupro_quick_view_event_action', 'Quick View')); ?>"  />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="rmenupro-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'Compatibility Settings'); ?>
                        <table class="form-table plugincy_table">

                            <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Load Scripts On', 'Control where quick view scripts are loaded to improve compatibility and performance.'); ?>
                                <td class="rmenupro-settings-control">
                                    <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_load_scripts' : 'rmenupro_quick_view_load_scripts'; ?>" class="rmenupro-select">
                                        <option value="all" <?php selected(get_option('rmenupro_quick_view_load_scripts', 'wc-only'), 'all'); ?>>All Pages</option>
                                        <option value="wc-only" <?php selected(get_option('rmenupro_quick_view_load_scripts', 'wc-only'), 'wc-only'); ?>>WooCommerce Pages Only</option>
                                        <option value="specific" <?php selected(get_option('rmenupro_quick_view_load_scripts', 'wc-only'), 'specific'); ?>>Specific Pages Only</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>" id="rmenupro-specific-pages-row" style="<?php echo (get_option('rmenupro_quick_view_load_scripts', 'wc-only') == 'specific') ? 'display:block;' : 'display:none;'; ?>">
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Specific Pages IDs', 'Enter the IDs of the specific pages where you want to load the quick view.'); ?>
                                <td class="rmenupro-settings-control">
                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_quick_view_specific_pages' : 'rmenupro_quick_view_specific_pages'; ?>" value="<?php echo esc_attr(get_option('rmenupro_quick_view_specific_pages', '')); ?>"  />
                                    /td>
                            </tr>
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Theme Compatibility Mode', 'Enable this if you experience display issues with your theme.'); ?>
                                <td class="rmenupro-settings-control">
                                    <?php $onepaquc_helper->switcher('rmenupro_quick_view_theme_compat', 0); ?>
                                </td>
                            <tr>
                        </table>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        // Show/hide custom size fields based on modal size selection
                        $('#rmenupro-qv-style-select').on('change', function() {
                            if ($(this).val() === 'custom') {
                                $('#rmenupro-qv-custom-css-row').css('display', 'flex');
                            } else {
                                $('#rmenupro-qv-custom-css-row').hide();
                            }
                        });

                        // Show/hide specific pages field
                        $('select[name="rmenupro_quick_view_load_scripts"]').on('change', function() {
                            if ($(this).val() === 'specific') {
                                $('#rmenupro-specific-pages-row').show();
                            } else {
                                $('#rmenupro-specific-pages-row').hide();
                            }
                        });

                        // Show/hide custom size fields based on modal size selection
                        $('select[name="rmenupro_quick_view_modal_size"]').on('change', function() {
                            if ($(this).val() === 'custom') {
                                $('.rmenupro-custom-size-row').show();
                            } else {
                                $('.rmenupro-custom-size-row').hide();
                            }
                        });
                    });
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tab click handler for Add To Cart settings tabs
                        const tabItems = document.querySelectorAll('#tab-7 .rmenupro-settings-tab-item');
                        const tabContents = document.querySelectorAll('#tab-7 > .tab-content');
                        const enableCustomQuickView = document.querySelector('input[name="rmenupro_enable_quick_view"]');
                        const highlight_quick_view_enable_Section = document.querySelector('#rmenupro-quick-view-enable-field');
                        const highlight_popup_content_element_Section = document.querySelector('#rmenupro-quick-view-content-elements');
                        const highlight_quick_view_display_Section = document.querySelector('#rmenupro-quick-view-display-settings');
                        const productTypes = document.querySelectorAll('input[name="rmenupro_show_quick_view_by_types[]"]');
                        const productPages = document.querySelectorAll('input[name="rmenupro_show_quick_view_by_page[]"]');

                        tabItems.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs
                                tabItems.forEach(function(t) {
                                    t.classList.remove('active');
                                });
                                // Hide all tab contents
                                tabContents.forEach(function(content) {
                                    content.style.display = 'none';
                                });

                                // Add active class to clicked tab
                                tab.classList.add('active');
                                // Show the corresponding tab content
                                const tabId = tab.getAttribute('data-tab');
                                if (tabId !== "quick-general-settings" && enableCustomQuickView && !enableCustomQuickView.checked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_enable_Section,
                                        '<b>Enable Custom Quick View</b> in the general settings tab to access these options.'
                                    );
                                }
                                const content = document.getElementById(tabId);
                                if (content) {
                                    content.style.display = 'block';
                                }
                            });
                        });

                        enableCustomQuickView.addEventListener('change', function() {
                            if (this.checked) {
                                removeDirectCheckoutWarning(highlight_quick_view_enable_Section);
                                // in rmenupro_quick_view_content_elements[] if no checkbox is checked. show a warning
                                const contentElements = document.querySelectorAll('input[name="rmenupro_quick_view_content_elements[]"]');
                                let isAnyChecked = false;
                                contentElements.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyChecked = true;
                                    }
                                });
                                if (!isAnyChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_popup_content_element_Section,
                                        '<b>Select at least one content element</b> in the Popup Manage tab to view the changes.'
                                    );
                                }

                                // if rmenupro_show_quick_view_by_types[] & rmenupro_show_quick_view_by_page[] are empty, show a warning
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked && !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                }
                            }
                        });

                        // on changes in each productPages & productTypes. if productPages & productTypes  are empty show warning
                        productTypes.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked || !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                } else {
                                    removeDirectCheckoutWarning(highlight_quick_view_display_Section);
                                }
                            });
                        });
                        productPages.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked || !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                } else {
                                    removeDirectCheckoutWarning(highlight_quick_view_display_Section);
                                }
                            });
                        });



                        // on changes in rmenupro_quick_view_content_elements[] if no checkbox is checked. show a warning
                        const contentElements = document.querySelectorAll('input[name="rmenupro_quick_view_content_elements[]"]');
                        contentElements.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyChecked = false;
                                contentElements.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyChecked = true;
                                        removeDirectCheckoutWarning(highlight_popup_content_element_Section);
                                    }
                                });
                                if (!isAnyChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_popup_content_element_Section,
                                        '<b>Select at least one content element</b> in the Popup Manage tab to view the changes.'
                                    );
                                }

                                // if rmenupro_show_quick_view_by_types[] & rmenupro_show_quick_view_by_page[] are empty, show a warning                                
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked && !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                }
                            });
                        });

                        // Show only the first tab content by default
                        tabContents.forEach(function(content, idx) {
                            content.style.display = (idx === 0) ? 'block' : 'none';
                        });
                    });
                </script>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                    const enableCheckout = document.querySelector('div#tab-7 input[name="rmenupro_enable_quick_view"]');
                    // if rmenupro_quick_view_display_type is icon, disable the rmenupro_quick_view_button_text
                    const quickViewDisplayType = document.querySelector('div#tab-7 select[name="rmenupro_quick_view_display_type"]');
                    const quickViewButtonText = document.querySelector('div#tab-7 input[name="rmenupro_quick_view_button_text"]');
                    // if rmenupro_quick_view_button_icon is none, show warning
                    const quickViewButtonIcon = document.querySelector('div#tab-7 select[name="rmenupro_quick_view_button_icon"]');
                    const heighlight_quick_view_button_style = document.querySelector('#rmenupro-quick-view-button-style-section');
                    quickViewDisplayType.addEventListener('change', function() {
                        toggleDisabledClass(this.value === 'icon', quickViewButtonText);

                        if (this.value !== 'button') {
                            if (quickViewButtonIcon.value === 'none') {
                                showDirectCheckoutWarning(
                                    heighlight_quick_view_button_style,
                                    'Please select an icon for the Quick View button.'
                                );
                            }
                        } else {
                            removeDirectCheckoutWarning(heighlight_quick_view_button_style);
                        }
                    });

                    // Trigger change event on page load to set initial visibility
                    quickViewDisplayType.dispatchEvent(new Event('change'));

                    quickViewButtonIcon.addEventListener('change', function() {
                        if (this.value === 'none' && quickViewDisplayType.value !== 'button') {
                            showDirectCheckoutWarning(
                                heighlight_quick_view_button_style,
                                'Please select an icon for the Quick View button.'
                            );
                        } else {
                            removeDirectCheckoutWarning(heighlight_quick_view_button_style);
                        }
                    });

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-7 input, div#tab-7 select')).filter(
                        el => !(el.name === "rmenupro_enable_quick_view")
                    );

                    toggleDisabledClass(!enableCheckout.checked, allinputFields);
                    enableCheckout.addEventListener('change', function() {
                        toggleDisabledClass(!this.checked, allinputFields);
                    });

                    // Show/hide custom CSS row based on selected style after page load
                    const styleSelect = document.querySelector('select[name="rmenupro_quick_view_button_style"]');
                    const customCssRow = document.querySelector('textarea[name="rmenupro_quick_view_custom_css"]').closest('tr');
                    // if rmenupro_quick_view_display_type is button, hide the rmenupro_quick_view_button_icon & rmenupro_quick_view_icon_position
                    const quickViewButtonIconPos = document.querySelector('div#tab-7 select[name="rmenupro_quick_view_icon_position"]');
                    const quickViewButtonIconRow = quickViewButtonIcon.closest('tr');
                    const quickViewButtonIconPosRow = quickViewButtonIconPos.closest('tr');
                    const updateQuickViewDisplayType = () => {
                        if (quickViewDisplayType.value === 'button') {
                            quickViewButtonIconRow.style.display = 'none';
                            quickViewButtonIconPosRow.style.display = 'none';
                        } else if (quickViewDisplayType.value === 'icon') {
                            quickViewButtonIconRow.style.display = 'flex';
                            quickViewButtonIconPosRow.style.display = 'none';
                        } else {
                            quickViewButtonIconRow.style.display = 'flex';
                            quickViewButtonIconPosRow.style.display = 'flex';
                        }
                    };

                    const updateCustomCssRowVisibility = () => {
                        if (styleSelect.value === 'custom') {
                            customCssRow.style.display = 'block';
                        } else {
                            customCssRow.style.display = 'none';
                        }
                        updateQuickViewDisplayType();
                    };

                    // Initial check
                    updateCustomCssRowVisibility();

                    // Add change event listener
                    styleSelect.addEventListener('change', updateCustomCssRowVisibility);

                    updateQuickViewDisplayType();
                    quickViewDisplayType.addEventListener('change', updateQuickViewDisplayType);
                });
            </script>
            <div class="tab-content" id="tab-8">
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-cart"></span>', 'WooCommerce Add To Cart', '', 'Easily modify the Add to Cart button text, style, and functionality for a more engaging shopping experience.'); ?>
                    <div class="rmenupro-settings-tabs">
                        <ul class="rmenupro-settings-tab-list">
                            <li class="rmenupro-settings-tab-item active" data-tab="general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenupro-settings-tab-item" data-tab="button-behavior">
                                <span class="dashicons dashicons-controls-play"></span> Button Behavior
                            </li>
                            <li class="rmenupro-settings-tab-item" data-tab="advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content" id="general-settings" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr id="rmenupro-enable-custom-add-to-cart">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Customizable Add to Cart', 'Enable or disable custom Add to Cart styling and functionality.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_enable_custom_add_to_cart', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Button Text', 'Customize the text displayed on the Add to Cart button for simple products.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="txt-add-to-cart" value="<?php echo esc_attr(get_option('txt-add-to-cart', 'Add to Cart')); ?>"  />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Variable Product Button Text', 'Customize the text displayed on the Add to Cart button for variable products.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="txt-select-options" value="<?php echo esc_attr(get_option('txt-select-options', 'Select Options')); ?>"  />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Read More Button Text', 'Customize the text displayed on the Read More button.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="txt-read-more" value="<?php echo esc_attr(get_option('txt-read-more', 'Select Options')); ?>"  />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Grouped Product Button Text', 'Customize the text displayed on the Add to Cart button for grouped products.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input type="text" name="rmenupro_grouped_add_to_cart_text" value="<?php echo esc_attr(get_option('rmenupro_grouped_add_to_cart_text', 'View Products')); ?>"  />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenupro-settings-section button-style-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Button Style', ''); ?>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_add_to_cart_style" class="rmenupro-select" id="rmenupro-atc-style-select">
                                                <option value="default" <?php selected(get_option('rmenupro_add_to_cart_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option value="modern" <?php selected(get_option('rmenupro_add_to_cart_style', 'default'), 'modern'); ?>>Modern Style</option>
                                                <option value="rounded" <?php selected(get_option('rmenupro_add_to_cart_style', 'default'), 'rounded'); ?>>Rounded Style</option>
                                                <option value="minimal" <?php selected(get_option('rmenupro_add_to_cart_style', 'default'), 'minimal'); ?>>Minimal Style</option>
                                                <option value="custom" <?php selected(get_option('rmenupro_add_to_cart_style', 'default'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Button Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_add_to_cart_bg_color" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_bg_color', '#000')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Text Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_add_to_cart_text_color" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_text_color', '#ffffff')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Hover Background Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_add_to_cart_hover_bg_color" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_hover_bg_color', '#7f4579')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Hover Text Color</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="color" name="rmenupro_add_to_cart_hover_text_color" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_hover_text_color', '#ffffff')); ?>" class="rmenupro-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Border Radius</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_add_to_cart_border_radius" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_border_radius', '3')); ?>" class="small-text" min="0" max="50" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Button Font Size</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_add_to_cart_font_size" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_font_size', '14')); ?>" class="small-text" min="10" max="24" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenupro-settings-label">Button Width</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_add_to_cart_width" class="rmenupro-select">
                                                <option value="auto" <?php selected(get_option('rmenupro_add_to_cart_width', 'auto'), 'auto'); ?>>Auto</option>
                                                <option value="full" <?php selected(get_option('rmenupro_add_to_cart_width', 'auto'), 'full'); ?>>Full Width</option>
                                                <option value="custom" <?php selected(get_option('rmenupro_add_to_cart_width', 'auto'), 'custom'); ?>>Custom Width</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr id="rmenupro-atc-custom-width-row" style="<?php echo (get_option('rmenupro_add_to_cart_width', 'auto') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                                        <th class="rmenupro-settings-label">Custom Width Value</th>
                                        <td class="rmenupro-settings-control">
                                            <input type="number" name="rmenupro_add_to_cart_custom_width" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_custom_width', '150')); ?>" class="small-text" min="50" max="500" step="1" />
                                            <span class="rmenupro-unit">px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenupro-settings-label">Button Icon</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_add_to_cart_icon" class="rmenupro-select">
                                                <option value="none" <?php selected(get_option('rmenupro_add_to_cart_icon', 'none'), 'none'); ?>>No Icon</option>
                                                <option value="cart" <?php selected(get_option('rmenupro_add_to_cart_icon', 'none'), 'cart'); ?>>Cart Icon</option>
                                                <option value="plus" <?php selected(get_option('rmenupro_add_to_cart_icon', 'none'), 'plus'); ?>>Plus Icon</option>
                                                <option value="bag" <?php selected(get_option('rmenupro_add_to_cart_icon', 'none'), 'bag'); ?>>Shopping Bag Icon</option>
                                                <option value="basket" <?php selected(get_option('rmenupro_add_to_cart_icon', 'none'), 'basket'); ?>>Basket Icon</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="rmenupro-atc-icon-position-row" style="<?php echo (get_option('rmenupro_add_to_cart_icon', 'none') == 'none') ? 'display:none;' : 'display:block;'; ?>">
                                        <th class="rmenupro-settings-label">Icon Position</th>
                                        <td class="rmenupro-settings-control">
                                            <select name="rmenupro_add_to_cart_icon_position" class="rmenupro-select">
                                                <option value="left" <?php selected(get_option('rmenupro_add_to_cart_icon_position', 'left'), 'left'); ?>>Left</option>
                                                <option value="right" <?php selected(get_option('rmenupro_add_to_cart_icon_position', 'left'), 'right'); ?>>Right</option>
                                                <option value="top" <?php selected(get_option('rmenupro_add_to_cart_icon_position', 'left'), 'top'); ?>>Top</option>
                                                <option value="bottom" <?php selected(get_option('rmenupro_add_to_cart_icon_position', 'left'), 'bottom'); ?>>Bottom</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="rmenupro-settings-row rmenupro-custom-css-row" id="rmenupro-atc-custom-css-row" style="<?php echo (get_option('rmenupro_add_to_cart_style', 'default') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <th class="rmenupro-settings-label">Custom CSS</th>
                                        <td class="rmenupro-settings-control" style="max-width: 100% !important;">
                                            <textarea name="rmenupro_add_to_cart_custom_css" class="rmenupro-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenupro_add_to_cart_custom_css', '')); ?></textarea>
                                            <p class="rmenupro-field-description">Add custom CSS for advanced button styling. Use the class <code>.product_type_simple.add_to_cart_button</code> to target the button.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-8 select[name="rmenupro_add_to_cart_style"]');

                            const buttonStyleSection = document.querySelector('.rmenupro-settings-section.button-style-section table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);
                            const customWidthRow = document.getElementById('rmenupro-atc-custom-width-row');
                            // if rmenupro_add_to_cart_icon is none, hide the rmenupro-atc-icon-position-row
                            const iconPositionRow = document.getElementById('rmenupro-atc-icon-position-row');
                            const iconSelect = document.querySelector('select[name="rmenupro_add_to_cart_icon"]');
                            const customwidth = document.querySelector('select[name="rmenupro_add_to_cart_width"]');

                            if (iconSelect && iconPositionRow) {
                                iconSelect.addEventListener('change', function() {
                                    if (this.value === 'none') {
                                        iconPositionRow.style.display = 'none';
                                    } else {
                                        iconPositionRow.style.display = 'flex';
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                setTimeout(() => {
                                    iconSelect.dispatchEvent(new Event('change'));
                                }, 1000);
                            }

                            if (customWidthRow) {
                                allFields.splice(allFields.indexOf(customWidthRow), 1); // Remove custom width row from the list
                            }


                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                        if (this.value !== 'custom') {
                                            document.getElementById('rmenupro-atc-custom-css-row').style.display = 'none';
                                        } else {
                                            document.getElementById('rmenupro-atc-custom-css-row').style.display = 'block';
                                        }
                                        customwidth.dispatchEvent(new Event('change'));
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                        customWidthRow.style.display = 'none';
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            // if rmenupro_add_to_cart_bg_color (which is bg color) & rmenupro_add_to_cart_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenupro_add_to_cart_bg_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenupro_add_to_cart_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }

                            // if rmenupro_add_to_cart_hover_bg_color (which is bg color) & rmenupro_add_to_cart_hover_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutHoverColor = document.querySelector('input[name="rmenupro_add_to_cart_hover_bg_color"]');
                            const checkoutHoverTextColor = document.querySelector('input[name="rmenupro_add_to_cart_hover_text_color"]');
                            if (checkoutHoverColor && checkoutHoverTextColor) {
                                checkoutHoverColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutHoverTextColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                            }
                        });
                    </script>
                </div>

                <div class="tab-content" id="button-behavior" style="padding: 0;">
                    <div class="rmenupro-settings-section plugincy_card mb-4">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Display Settings', 'Control how Add to Cart buttons appear on product archive pages.'); ?>

                        <div class="rmenupro-settings-row">
                            <div class="rmenupro-settings-field" id="rmenupro-add-to-cart-archive-display" style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                                <label class="rmenupro-settings-label">Button Display
                                    <span class="tooltip">
                                        <span class="question-mark">?</span>
                                        <span class="tooltip-text">Control how Add to Cart buttons appear on product archive pages.</span>
                                    </span>
                                </label>
                                <div class="rmenupro-settings-control">
                                    <select name="rmenupro_add_to_cart_catalog_display" class="rmenupro-select">
                                        <option value="default" <?php selected(get_option('rmenupro_add_to_cart_catalog_display', 'default'), 'default'); ?>>Default (WooCommerce Setting)</option>
                                        <option value="show" <?php selected(get_option('rmenupro_add_to_cart_catalog_display', 'default'), 'show'); ?>>Always Show</option>
                                        <option value="hide" <?php selected(get_option('rmenupro_add_to_cart_catalog_display', 'default'), 'hide'); ?>>Always Hide</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="plugincy_row">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5" id="add_to_cart_behave">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-controls-play"></span>', 'Add To Cart Behavior', 'Control how Add to Cart buttons behave on product archive pages.'); ?>
                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Enable AJAX Add to Cart', 'Add products to cart without page reload using AJAX.'); ?>
                                    <td>
                                        <?php $onepaquc_helper->switcher('rmenupro_enable_ajax_add_to_cart', 1); ?>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Default Quantity', 'Set the default quantity when adding products to cart from archive pages.'); ?>
                                    <td>
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="number" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_default_qty' : 'rmenupro_add_to_cart_default_qty'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_default_qty', '1')); ?>" class="small-text" min="1" max="100" step="1" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Quantity Selector on Archives', 'Display quantity selector on shop/archive pages before adding to cart.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_show_quantity_archive', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Redirect After Add to Cart', 'Choose whether to redirect customers after adding products to cart.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <select name="rmenupro_redirect_after_add" class="rmenupro-select">
                                            <option value="none" <?php selected(get_option('rmenupro_redirect_after_add', 'none'), 'none'); ?>>No Redirect</option>
                                            <!-- rmenupro_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                            <?php
                                            $disable_cart_page = get_option('rmenupro_disable_cart_page', '0');
                                            ?>
                                            <option value="cart" <?php selected(get_option('rmenupro_redirect_after_add', 'none'), 'cart'); ?> <?php echo ($disable_cart_page == '1') ? 'disabled' : ''; ?>>Cart Page <?php echo ($disable_cart_page == '1') ? '(Disabled)' : ''; ?></option>
                                            <option value="checkout" <?php selected(get_option('rmenupro_redirect_after_add', 'none'), 'checkout'); ?>>Checkout Page</option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- <tr>
                                    <?php //$onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Add to Cart Animation', 'Choose the animation effect when products are added to cart.'); 
                                    ?>
                                    <td class="rmenupro-settings-control">
                                        <select name="rmenupro_add_to_cart_animation" class="rmenupro-select">
                                            <option value="none" <?php //selected(get_option('rmenupro_add_to_cart_animation', 'slide'), 'none'); 
                                                                    ?>>None</option>
                                            <option value="slide" <?php //selected(get_option('rmenupro_add_to_cart_animation', 'slide'), 'slide'); 
                                                                    ?>>Slide Effect</option>
                                            <option value="fade" <?php //selected(get_option('rmenupro_add_to_cart_animation', 'slide'), 'fade'); 
                                                                    ?>>Fade Effect</option>
                                            <option value="fly" <?php //selected(get_option('rmenupro_add_to_cart_animation', 'slide'), 'fly'); 
                                                                ?>>Fly to Cart Effect</option>
                                        </select>
                                    </td>
                                </tr> -->
                            </table>
                        </div>

                        <div class="rmenupro-settings-section  plugincy_card plugincy_col-5" id="add_to_cart_notification">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-bell"></span>', 'Notifications', 'Customize how notifications are displayed when products are added to cart.'); ?>
                            <?php
                            $notification_style = get_option('rmenupro_add_to_cart_notification_style', 'default');
                            $toast_position = get_option('rmenupro_add_to_cart_toast_position', 'top_right');
                            $toast_size = get_option('rmenupro_add_to_cart_toast_size', 'comfy');
                            $toast_position_name = !onepaqucpro_premium_feature() ? 'pro_add_to_cart_toast_position' : 'rmenupro_add_to_cart_toast_position';
                            $toast_size_name = !onepaqucpro_premium_feature() ? 'pro_add_to_cart_toast_size' : 'rmenupro_add_to_cart_toast_size';
                            ?>
                            <table class="form-table plugincy_table">
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Notification Style', 'Choose how to display notifications when products are added to cart.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_notification_style' : 'rmenupro_add_to_cart_notification_style'; ?>" class="rmenupro-select">
                                            <option value="default" <?php selected(get_option('rmenupro_add_to_cart_notification_style', 'default'), 'default'); ?>>Default WooCommerce Notices</option>
                                            <option value="popup" <?php selected(get_option('rmenupro_add_to_cart_notification_style', 'default'), 'popup'); ?>>Popup Message</option>
                                            <option value="toast" <?php selected(get_option('rmenupro_add_to_cart_notification_style', 'default'), 'toast'); ?>>Toast Notification</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Success Message', 'Customize the success message shown after adding to cart. Use {product} as a placeholder for the product name.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_success_message' : 'rmenupro_add_to_cart_success_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_success_message', '{product} has been added to your cart.')); ?>"  />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'View Cart Link Text', 'Customize the View Cart link text shown inside popup and toast notifications.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_view_cart_text' : 'rmenupro_add_to_cart_view_cart_text'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_view_cart_text', 'View Cart')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Checkout Link Text', 'Customize the Checkout link text shown inside popup and toast notifications.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_checkout_text' : 'rmenupro_add_to_cart_checkout_text'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_checkout_text', 'Checkout')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Variation Required Alert', 'Customize the alert shown when a variable product is added without selecting options.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_variation_required_message' : 'rmenupro_add_to_cart_variation_required_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_variation_required_message', 'Please select some product options before adding this product to your cart.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'All Options Required Alert', 'Customize the alert shown when no complete variation selection exists.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_all_options_required_message' : 'rmenupro_add_to_cart_all_options_required_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_all_options_required_message', 'Please select all product options before adding this product to your cart.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Partial Options Alert', 'Customize the alert shown when only some variation options are selected. Use {selected} and {required}.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_partial_options_required_message' : 'rmenupro_add_to_cart_partial_options_required_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_partial_options_required_message', 'Please select all product options. You have selected {selected} out of {required} required options.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Complete Options Alert', 'Customize the alert shown when selected variation options contain an empty value.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_complete_options_message' : 'rmenupro_add_to_cart_complete_options_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_complete_options_message', 'Please complete all product option selections.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Add to Cart Error Message', 'Customize the error shown when an AJAX add-to-cart request fails validation.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_error_message' : 'rmenupro_add_to_cart_error_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_error_message', 'Error adding to cart')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Server Error Message', 'Customize the error shown when the AJAX add-to-cart request cannot reach the server.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_server_error_message' : 'rmenupro_add_to_cart_server_error_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_server_error_message', 'Server error. Please try again.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Clear Cart Error Message', 'Customize the alert shown when the plugin cannot clear the cart before add-to-cart/direct checkout.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_clear_cart_error_message' : 'rmenupro_clear_cart_error_message'; ?>" value="<?php echo esc_attr(get_option('rmenupro_clear_cart_error_message', 'Could not clear cart. Please try again.')); ?>" />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>rmenupro-notification-dependent<?php echo $notification_style === 'default' ? ' rmenupro-hidden-setting' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show View Cart Link', 'Display a "View Cart" link in the notification message.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_show_view_cart_link', 1); ?>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>rmenupro-notification-dependent<?php echo $notification_style === 'default' ? ' rmenupro-hidden-setting' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show Checkout Link', 'Display a "Checkout" link in the notification message.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_show_checkout_link', 0); ?>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>rmenupro-notification-dependent<?php echo $notification_style === 'default' ? ' rmenupro-hidden-setting' : ''; ?>" id="rmenupro-add-to-cart-notification-settings">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Notification Duration', 'How long to display the notification for (in milliseconds).'); ?>
                                    <td class="rmenupro-settings-control">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="number" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_notification_duration' : 'rmenupro_add_to_cart_notification_duration'; ?>" value="<?php echo esc_attr(get_option('rmenupro_add_to_cart_notification_duration', '3000')); ?>" class="small-text" min="1000" max="10000" step="500" />
                                        <span class="rmenupro-unit">ms</span>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>rmenupro-notification-dependent rmenupro-toast-only rmenupro-toast-layout-row<?php echo $notification_style !== 'toast' ? ' rmenupro-hidden-setting' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Floating Position', 'Choose where toast notifications should appear on the screen.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <div class="rmenupro-toast-choice-wrapper">
                                            <div class="rmenupro-toast-choice-grid">
                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_position_name); ?>" value="bottom_right" <?php checked($toast_position, 'bottom_right'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--position rmenupro-toast-choice-preview--bottom-right">
                                                            <span class="rmenupro-toast-choice-toast"></span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Bottom right</span>
                                                    </span>
                                                </label>

                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_position_name); ?>" value="top_right" <?php checked($toast_position, 'top_right'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--position rmenupro-toast-choice-preview--top-right">
                                                            <span class="rmenupro-toast-choice-toast"></span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Top right</span>
                                                    </span>
                                                </label>

                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_position_name); ?>" value="bottom_left" <?php checked($toast_position, 'bottom_left'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--position rmenupro-toast-choice-preview--bottom-left">
                                                            <span class="rmenupro-toast-choice-toast"></span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Bottom left</span>
                                                    </span>
                                                </label>

                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_position_name); ?>" value="top_left" <?php checked($toast_position, 'top_left'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--position rmenupro-toast-choice-preview--top-left">
                                                            <span class="rmenupro-toast-choice-toast"></span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Top left</span>
                                                    </span>
                                                </label>
                                            </div>
                                            <p class="description rmenupro-toast-choice-description">Choose where floating alerts should appear.</p>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only ' : ''; ?>rmenupro-notification-dependent rmenupro-toast-only rmenupro-toast-layout-row<?php echo $notification_style !== 'toast' ? ' rmenupro-hidden-setting' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Size', 'Choose the size of toast notifications.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <div class="rmenupro-toast-choice-wrapper">
                                            <div class="rmenupro-toast-choice-grid">
                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_size_name); ?>" value="comfy" <?php checked($toast_size, 'comfy'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--size rmenupro-toast-choice-preview--comfy">
                                                            <span class="rmenupro-toast-choice-toast-card">
                                                                <span class="rmenupro-toast-choice-avatar"></span>
                                                                <span class="rmenupro-toast-choice-lines">
                                                                    <span></span>
                                                                    <span></span>
                                                                    <span></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Comfy</span>
                                                    </span>
                                                </label>

                                                <label class="rmenupro-toast-choice-card">
                                                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="radio" name="<?php echo esc_attr($toast_size_name); ?>" value="compact" <?php checked($toast_size, 'compact'); ?> />
                                                    <span class="rmenupro-toast-choice-card-inner">
                                                        <span class="rmenupro-toast-choice-preview rmenupro-toast-choice-preview--size rmenupro-toast-choice-preview--compact">
                                                            <span class="rmenupro-toast-choice-toast-card">
                                                                <span class="rmenupro-toast-choice-avatar"></span>
                                                                <span class="rmenupro-toast-choice-lines">
                                                                    <span></span>
                                                                    <span></span>
                                                                    <span></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                        <span class="rmenupro-toast-choice-title">Compact</span>
                                                    </span>
                                                </label>
                                            </div>
                                            <p class="description rmenupro-toast-choice-description">Choose the size of floating notifications.</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const checkbox = document.querySelector('input[name="rmenupro_enable_ajax_add_to_cart"]');
                            const redirect_atc = document.querySelector('select[name="rmenupro_redirect_after_add"]');
                            const notificationStyle = document.querySelector('select[name="rmenupro_add_to_cart_notification_style"], select[name="pro_add_to_cart_notification_style"]');
                            const settingsInputs = Array.from(document.querySelectorAll('#add_to_cart_behave input, #add_to_cart_behave select, #add_to_cart_notification input, #add_to_cart_notification select')).filter(
                                el => !(el.name === "rmenupro_enable_ajax_add_to_cart")
                            );
                            const notificationDependentRows = Array.from(document.querySelectorAll('#add_to_cart_notification .rmenupro-notification-dependent'));
                            const toastOnlyRows = Array.from(document.querySelectorAll('#add_to_cart_notification .rmenupro-toast-only'));

                            function updateSettings() {
                                const enabled_checkbox = document.querySelector('input[name="rmenupro_enable_ajax_add_to_cart"]').checked;

                                toggleDisabledClass(!enabled_checkbox, settingsInputs);

                            }

                            function updateNotificationRowVisibility() {
                                if (!notificationStyle) {
                                    return;
                                }

                                const currentStyle = notificationStyle.value;
                                const showNotificationSettings = currentStyle !== 'default';

                                notificationDependentRows.forEach(function(row) {
                                    row.classList.toggle('rmenupro-hidden-setting', !showNotificationSettings);
                                });

                                toastOnlyRows.forEach(function(row) {
                                    row.classList.toggle('rmenupro-hidden-setting', currentStyle !== 'toast');
                                });
                            }

                            function notificationVisibilityHandle() {
                                const redirectAtcVal = document.querySelector('select[name="rmenupro_redirect_after_add"]').value; // Use .value

                                const notiSettings = Array.from(document.querySelectorAll('#add_to_cart_notification input, #add_to_cart_notification select'));
                                toggleDisabledClass(redirectAtcVal !== 'none', notiSettings);
                                updateNotificationRowVisibility();
                            }

                            // Initial update on page load
                            updateSettings();
                            notificationVisibilityHandle();

                            // Update when the checkbox changes
                            if (checkbox) {
                                checkbox.addEventListener('change', updateSettings);
                            }

                            if (redirect_atc) {
                                redirect_atc.addEventListener('change', notificationVisibilityHandle);
                            }

                            if (notificationStyle) {
                                notificationStyle.addEventListener('change', updateNotificationRowVisibility);
                            }
                        });
                    </script>
                </div>

                <div class="tab-content" id="advanced" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-smartphone"></span>', 'Mobile Settings'); ?>
                            <table class="form-table plugincy_table">
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Sticky Add to Cart on Mobile', 'Show a sticky Add to Cart button at the bottom of single product pages on mobile devices only.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_sticky_add_to_cart_mobile', 0, true); ?>
                                    </td>
                                </tr>
                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Text (Coming Soon)', 'Set a different button text for mobile devices. Leave empty to use the default text.'); ?>
                                    <td class="rmenupro-settings-control disabled">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_mobile_add_to_cart_text' : 'rmenupro_mobile_add_to_cart_text'; ?>" value="<?php echo esc_attr(get_option('rmenupro_mobile_add_to_cart_text', '')); ?>"  />
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Size', 'Choose button size optimization for mobile devices.'); ?>
                                    <td class="rmenupro-settings-control">
                                        <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_mobile_button_size' : 'rmenupro_mobile_button_size'; ?>" class="rmenupro-select">
                                            <option value="default" <?php selected(get_option('rmenupro_mobile_button_size', 'default'), 'default'); ?>>Same as Desktop</option>
                                            <option value="larger" <?php selected(get_option('rmenupro_mobile_button_size', 'default'), 'larger'); ?>>Larger</option>
                                            <option value="smaller" <?php selected(get_option('rmenupro_mobile_button_size', 'default'), 'smaller'); ?>>Small</option>
                                            <option value="full" <?php selected(get_option('rmenupro_mobile_button_size', 'default'), 'full'); ?>>Full Width</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Icon Only (Coming Soon)', 'Show only the icon (without text) on mobile devices to save space.'); ?>
                                    <td class="rmenupro-settings-control disabled">
                                        <?php $onepaquc_helper->switcher('rmenupro_mobile_icon_only', 0, true); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenupro-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-welcome-widgets-menus"></span>', 'Advanced Options', ''); ?>

                            <table class="form-table plugincy_table">
                                <!-- <tr class="<?php //echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; 
                                                ?>">
                                    <?php //$onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Add to Cart Load Effect', 'Choose an animation effect while adding to cart is in progress.'); 
                                    ?>
                                    <td class="rmenupro-settings-control">
                                        <select <?php //echo !onepaqucpro_premium_feature() ? 'disabled' : ''; 
                                                ?> name="<?php //echo !onepaqucpro_premium_feature() ? 'pro_add_to_cart_loading_effect' : 'rmenupro_add_to_cart_loading_effect'; 
                                                            ?>" class="rmenupro-select">
                                            <option value="none" <?php //selected(get_option('rmenupro_add_to_cart_loading_effect', 'spinner'), 'none'); 
                                                                    ?>>None</option>
                                            <option value="spinner" <?php //selected(get_option('rmenupro_add_to_cart_loading_effect', 'spinner'), 'spinner'); 
                                                                    ?>>Spinner</option>
                                            <option value="dots" <?php //selected(get_option('rmenupro_add_to_cart_loading_effect', 'spinner'), 'dots'); 
                                                                    ?>>Dots</option>
                                            <option value="pulse" <?php //selected(get_option('rmenupro_add_to_cart_loading_effect', 'spinner'), 'pulse'); 
                                                                    ?>>Pulse</option>
                                        </select>
                                    </td>
                                </tr> -->
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Disable continue shopping button', 'WooCommerce shows a continue shopping button after a product is added to cart, with this option you can disable that link so user remain on checkout page'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenupro_disable_btn_out_of_stock', 1); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="rmenupro-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-translation"></span>', 'Compatibility Settings', ''); ?>
                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Force Button CSS', 'Use !important CSS rules to override theme styling (use only if needed).'); ?>
                                <td class="rmenu-settings-control">
                                    <?php $onepaquc_helper->switcher('rmenupro_force_button_css', 0); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tab click handler for Add To Cart settings tabs
                        const tabItems = document.querySelectorAll('#tab-8 .rmenupro-settings-tab-item');
                        const tabContents = document.querySelectorAll('#tab-8  .tab-content');
                        const btn_display = document.querySelector('select[name="rmenupro_add_to_cart_catalog_display"]');

                        btn_display.addEventListener('change', function() {
                            if (this.value === 'hide') {
                                // disable all inputs in the tab except btn_display
                                tabContents.forEach(function(content) {
                                    const inputs = content.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        toggleDisabledClass(input !== btn_display, input);
                                    });
                                });
                            } else {
                                // enable all inputs in the tab
                                tabContents.forEach(function(content) {
                                    const inputs = content.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        toggleDisabledClass(false, input);
                                    });
                                });
                            }
                        });

                        tabItems.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs
                                tabItems.forEach(function(t) {
                                    t.classList.remove('active');
                                });
                                // Hide all tab contents
                                tabContents.forEach(function(content) {
                                    content.style.display = 'none';
                                });

                                // Add active class to clicked tab
                                tab.classList.add('active');
                                // Show the corresponding tab content
                                const tabId = tab.getAttribute('data-tab');
                                const highlight_add_to_cart_enable_Section = document.querySelector('#rmenupro-enable-custom-add-to-cart');
                                // If "Enable Custom Add to Cart" is not enabled, show a popup message and prevent tab switching
                                const enableCustomAddToCart = document.querySelector('input[name="rmenupro_enable_custom_add_to_cart"]');
                                if (tabId !== "general-settings" && enableCustomAddToCart && !enableCustomAddToCart.checked) {
                                    showDirectCheckoutWarning(
                                        highlight_add_to_cart_enable_Section,
                                        '<b>Enable Custom Add to Cart</b> in the general settings tab to access these options.'
                                    );
                                }
                                enableCustomAddToCart.addEventListener('change', function() {
                                    if (this.checked) {
                                        removeDirectCheckoutWarning(highlight_add_to_cart_enable_Section);
                                    }
                                });

                                const highlight_rmenupro_add_to_cart_archive_display_Section = document.querySelector('#rmenupro-add-to-cart-archive-display');

                                // If the tab is not "button-behavior" and the button display is set to hide, show a popup message
                                if (tabId !== "button-behavior" && btn_display && btn_display.value === 'hide') {
                                    showDirectCheckoutWarning(
                                        highlight_rmenupro_add_to_cart_archive_display_Section,
                                        '<b>Button Display is set to Hide</b>. You can change this in the Button Behavior -> Display Settings.'
                                    );
                                }
                                btn_display.addEventListener('change', function() {
                                    if (this.value !== 'hide') {
                                        removeDirectCheckoutWarning(highlight_rmenupro_add_to_cart_archive_display_Section);
                                    }
                                });

                                const content = document.getElementById(tabId);
                                if (content) {
                                    content.style.display = 'block';
                                }
                            });
                        });

                        // Show only the first tab content by default
                        tabContents.forEach(function(content, idx) {
                            content.style.display = (idx === 0) ? 'block' : 'none';
                        });
                    });
                </script>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                    const enableCheckout = document.querySelector('div#tab-8 input[name="rmenupro_enable_custom_add_to_cart"]');
                    const btn_display = document.querySelector('select[name="rmenupro_add_to_cart_catalog_display"]');
                    const btn_display_hidden = btn_display && btn_display.value === 'hide';

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-8 input, div#tab-8 select, div#tab-8 textarea')).filter(
                        el => !(el.name === "rmenupro_enable_custom_add_to_cart" || el.name === "rmenupro_add_to_cart_catalog_display")
                    );
                    toggleDisabledClass(!enableCheckout.checked || btn_display_hidden, allinputFields);
                    enableCheckout.addEventListener('change', function() {
                        toggleDisabledClass(!this.checked, allinputFields);
                    });
                });
            </script>
            <div style="text-align: right;display: flex;justify-content: flex-end;width: 98.5%;margin-top: 42px;align-items: center;padding: 20px;box-sizing: border-box;">
                <button type="submit" class="button button-primary" style="padding: 4px 20px;border: none;display: flex;gap: 5px;align-items: center;">
                    <span style="margin-bottom: -7px;"><svg fill="#fff" width="16" height="16" viewBox="0 0 0.48 0.48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.474.124.356.006A.03.03 0 0 0 .341 0H.022A.02.02 0 0 0 0 .022v.436c0 .013.01.023.022.023h.436A.022.022 0 0 0 .481.459v-.32A.02.02 0 0 0 .475.124zM.131.044h.131V.16H.131zm0 .393V.32h.218v.116zm.306 0H.393V.299A.022.022 0 0 0 .371.276H.109a.02.02 0 0 0-.022.022v.138H.044V.044h.044v.139q.001.02.021.021h.174A.022.022 0 0 0 .306.182V.044h.027l.104.104z" />
                        </svg></span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
        <form class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>" method="post" action="" style="text-align: right;display: flex;justify-content: flex-end;width: 99%;align-items: center;padding: 0 2rem 2rem;box-sizing: border-box;" onsubmit="return confirm('Are you sure you want to reset all settings to default? This action cannot be undone.');">
            <?php wp_nonce_field('onepaquc_reset_settings', 'onepaquc_reset_settings_nonce'); ?>
            <input type="hidden" name="<?php echo !onepaqucpro_premium_feature() ? 'pro-onepaqucpro_reset' : 'onepaqucpro_reset_settings'; ?>" value="1">
            <?php
            $disabled = !onepaqucpro_premium_feature() ? array('disabled' => 'disabled') : array();
            submit_button('Reset Settings', 'button-primary', '', false, array_merge(array('style' => 'background:#dc3545;color:#fff;border-color:#dc3545;padding: 4px 27px;'), is_array($disabled) ? $disabled : []));
            ?>
        </form>


        <div>
            <div class="plugincy_nav_card mb-4" style="padding-bottom: 1px;">
                <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-video-alt3"></span>', 'Tutorials', '', 'Learn how to use our plugin features with these step-by-step video tutorials.'); ?>
            </div>

            <div class="tutorial-container">
                <!-- Tutorial Topic 1 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/IJU9EEIT8MA?autoplay=1&mute=1&loop=1&playlist=IJU9EEIT8MA"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Buy Now Button</h3>
                    <p>
                        Transform your <strong>WooCommerce store</strong> with <strong>instant purchase buttons</strong> that bypass the traditional <strong>cart process</strong>. Our plugin automatically adds <strong>buy now buttons</strong> on <strong>single product</strong> and <strong>archive pages</strong>, but you have complete control over placement and customization.
                    </p>

                    <div class="tutorial-shortcode">
                        <h4>Basic Shortcode:</h4>
                        <code>[onepaquc_button]</code>
                        <p>This shortcode will display a buy now button for the current product when used on product pages.</p>
                    </div>
                </div>

                <!-- Tutorial Topic 2 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/FY_-CSI03vk?autoplay=1&mute=1&loop=1&playlist=FY_-CSI03vk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Buy Now Button Anywhere</h3>
                    <p>
                        You can use <strong>buy now button</strong> anywhere on your <strong>site</strong> with this <strong>shortcode</strong>.
                    </p>
                    <div class="tutorial-shortcode">
                        <h4>Shortcode:</h4>
                        <code>[onepaquc_button product_id="123" variation_id="456" qty="2"]</code>
                    </div>
                    <div class="tutorial-tips">
                        <h4>Tips:</h4>
                        <p>Use our widget or block (Buy Now Button) for easier implementation.</p>
                    </div>
                </div>

                <!-- Tutorial Topic 3 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/nXmUvcNzDb8?autoplay=1&mute=1&loop=1&playlist=nXmUvcNzDb8"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>One Page Checkout</h3>
                    <p>
                        You can use <strong>one page checkout</strong> anywhere on your <strong>site</strong> with this <strong>shortcode</strong>.
                    </p>
                    <div class="tutorial-shortcode">
                        <h4>Shortcode:</h4>
                        <code>[onepaquc_checkout product_id="123" variation_id="456" qty="2" clear_cart="yes" auto_add="yes"]</code>
                    </div>
                    <div class="tutorial-tips">
                        <h4>Tips:</h4>
                        <p>Use our widget or block (One-page checkout) for easier implementation.</p>
                    </div>
                </div>

                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/S96e1Nj5Kxc?autoplay=1&mute=1&loop=1&playlist=S96e1Nj5Kxc"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Floating Cart</h3>
                    <p>
                        A <strong>modern</strong>, <strong>stylish</strong> <strong>side-drawer cart</strong> that users can access from <strong>any page</strong> for improved convenience.
                    </p>
                    <div class="tutorial-tips">
                        <h4>Floating Cart Benefits:</h4>
                        <ul>
                            <li><strong>Reduced Abandonment:</strong> Customers can review their cart without losing their place on your site</li>
                            <li><strong>Improved UX:</strong> No page reloads or redirects needed to manage cart contents</li>
                            <li><strong>Mobile Friendly:</strong> Touch-optimized interface perfect for mobile shopping</li>
                            <li><strong>Sales Boost:</strong> Easy access to cart encourages completion of purchases</li>
                        </ul>
                    </div>
                </div>
            </div>

            <style>
                .tutorial-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 25px;
                    margin-top: 20px;
                }

                .tutorial-topic {
                    background: #fff;
                    border: 1px solid #e5e5e5;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
                    transition: box-shadow 0.3s ease;
                }

                .tutorial-topic:hover {
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }

                .tutorial-video {
                    margin-bottom: 15px;
                    border-radius: 5px;
                    overflow: hidden;
                    background: #000;
                }

                .tutorial-video video {
                    width: 100%;
                    display: block;
                }

                .tutorial-topic h3 {
                    margin-top: 0;
                    margin-bottom: 10px;
                    color: #23282d;
                    font-size: 18px;
                }

                .tutorial-topic p {
                    margin-bottom: 15px;
                    color: #555;
                    line-height: 1.5;
                }

                .tutorial-shortcode,
                .tutorial-tips {
                    background: #f8f9fa;
                    border-left: 4px solid #0073aa;
                    padding: 12px 15px;
                    margin-top: 15px;
                    border-radius: 0 4px 4px 0;
                }

                .tutorial-shortcode h4,
                .tutorial-tips h4 {
                    margin-top: 0;
                    margin-bottom: 8px;
                    font-size: 14px;
                    color: #23282d;
                }

                .tutorial-shortcode code {
                    display: block;
                    background: #eaeaea;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-family: monospace;
                    font-size: 14px;
                    color: #0073aa;
                    overflow-x: auto;
                }

                .tutorial-tips p {
                    margin-bottom: 0;
                    font-size: 14px;
                }
            </style>
        </div>
    </div>
<?php
    // }
}
add_action('admin_init', 'onepaqucpro_cart_settings');
// add_action('wp_head', 'onepaqucpro_cart_custom_css');




function onepaqucpro_cart_settings()
{
    global $onepaqucpro_string_settings_fields, $onepaqucpro_floating_cart_premium_checkbox_fields, $onepaqucpro_floating_cart_premium_text_fields;
    foreach (onepaqucpro_rmenupro_fields() as $key => $field) {
        register_setting('onepaqucpro_cart_settings', $key, 'sanitize_text_field');
    }
    foreach (onepaqucpro_onpcheckout_heading() as $key => $field) {
        register_setting('onepaqucpro_cart_settings', $key, 'sanitize_text_field');
    }

    foreach ($onepaqucpro_string_settings_fields as $field) {
        $legacy_floating_text_fields = array('your_cart', 'txt_Select_All', 'txt_Selected', 'txt_subtotal', 'txt_total', 'txt_checkout', 'txt_you_may_like');
        if ($field === 'rmenu_cart_checkout_behavior') {
            $sanitize_callback = 'onepaqucpro_sanitize_floating_cart_checkout_behavior';
        } elseif ($field === 'rmenu_floating_cart_empty_icon') {
            $sanitize_callback = 'onepaqucpro_sanitize_floating_cart_empty_icon';
        } elseif ($field === 'rmenu_floating_cart_meta_include') {
            $sanitize_callback = 'onepaqucpro_sanitize_floating_cart_meta_rules';
        } elseif (is_array($onepaqucpro_floating_cart_premium_checkbox_fields) && array_key_exists($field, $onepaqucpro_floating_cart_premium_checkbox_fields)) {
            $sanitize_callback = 'onepaqucpro_sanitize_premium_checkbox';
        } elseif (
            is_array($onepaqucpro_floating_cart_premium_text_fields)
            && array_key_exists($field, $onepaqucpro_floating_cart_premium_text_fields)
            && !in_array($field, $legacy_floating_text_fields, true)
        ) {
            $sanitize_callback = 'onepaqucpro_sanitize_premium_text_field';
        } else {
            $sanitize_callback = 'sanitize_text_field';
        }
        register_setting('onepaqucpro_cart_settings', $field, $sanitize_callback);
    }

    global $onepaqucpro_checkoutformfields, $onepaqucpro_productpageformfields;
    $settings = array_merge(array_keys($onepaqucpro_checkoutformfields), array_keys($onepaqucpro_productpageformfields));

    foreach ($settings as $setting) {
        register_setting('onepaqucpro_cart_settings', $setting, 'sanitize_text_field');
    }
    // Register the setting for the checkout fields values of array
    register_setting('onepaqucpro_cart_settings', "onepaqucpro_checkout_fields", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_show_quick_checkout_by_types", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_show_quick_checkout_by_page", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_add_to_cart_by_types", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_quick_view_content_elements", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_show_quick_view_by_types", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "rmenupro_show_quick_view_by_page", 'onepaqucpro_sanitize_array_of_text');
    register_setting('onepaqucpro_cart_settings', "onepaqucpro_my_trust_badges_items", 'onepaqucpro_sanitize_trust_badges_items');
    register_setting('onepaqucpro_cart_settings', "checkout_form_setup", [
        'type' => 'string',
        'sanitize_callback' => function ($value) {
            // Allow HTML, CSS, JS (no sanitization)
            return $value;
        },
        'show_in_rest' => false,
    ]);
    register_setting('onepaqucpro_cart_settings', 'onepaqucpro_trust_badge_custom_html', [
        'type' => 'string',
        'sanitize_callback' => function ($value) {
            // Allow HTML, CSS, JS (no sanitization)
            return $value;
        },
        'show_in_rest' => false,
        'default' => '<!-- Custom Trust Badges HTML with CSS --> <div class="custom-trust-badges"> <!-- Payment Security Badge --> <div class="trust-badge payment-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect> <path d="M7 11V7a5 5 0 0 1 10 0v4"></path> </svg> </div> <div class="badge-content"> <h4>Secure Payment</h4> <p>Your payment information is encrypted</p> </div> </div> <!-- Money Back Guarantee Badge --> <div class="trust-badge guarantee-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="10"></circle> <path d="M8 14s1.5 2 4 2 4-2 4-2"></path> <line x1="9" y1="9" x2="9.01" y2="9"></line> <line x1="15" y1="9" x2="15.01" y2="9"></line> </svg> </div> <div class="badge-content"> <h4>30-Day Guarantee</h4> <p>Not satisfied? Get a full refund</p> </div> </div> <!-- Fast Shipping Badge --> <div class="trust-badge shipping-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="1" y="3" width="15" height="13"></rect> <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon> <circle cx="5.5" cy="18.5" r="2.5"></circle> <circle cx="18.5" cy="18.5" r="2.5"></circle> </svg> </div> <div class="badge-content"> <h4>Fast Shipping</h4> <p>Delivery within 2-4 business days</p> </div> </div> <!-- Privacy Badge --> <div class="trust-badge privacy-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path> </svg> </div> <div class="badge-content"> <h4>Privacy Protected</h4> <p>Your data is never shared with third parties</p> </div> </div> </div> <style> .custom-trust-badges { display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; margin: 30px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; } .custom-trust-badges .trust-badge { flex: 1; min-width: 200px; display: flex; align-items: center; padding: 15px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; position: relative; overflow: hidden; } .custom-trust-badges .trust-badge::before { content: \'\'; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: currentColor; opacity: 0.8; } .custom-trust-badges .trust-badge:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); } .custom-trust-badges .badge-icon { display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; flex-shrink: 0; } .custom-trust-badges .badge-icon svg { width: 28px; height: 28px; } .custom-trust-badges .badge-content { flex-grow: 1; } .custom-trust-badges .badge-content h4 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; } .custom-trust-badges .badge-content p { margin: 0; font-size: 13px; opacity: 0.7; line-height: 1.4; } ge specific colors */ .custom-trust-badges .payment-badge { color: #3498db; } .custom-trust-badges .payment-badge .badge-icon { background-color: rgba(52, 152, 219, 0.1); } .custom-trust-badges .guarantee-badge { color: #2ecc71; } .custom-trust-badges .guarantee-badge .badge-icon { background-color: rgba(46, 204, 113, 0.1); } .custom-trust-badges .shipping-badge { color: #e67e22; } .custom-trust-badges .shipping-badge .badge-icon { background-color: rgba(230, 126, 34, 0.1); } .custom-trust-badges .privacy-badge { color: #9b59b6; } .custom-trust-badges .privacy-badge .badge-icon { background-color: rgba(155, 89, 182, 0.1); } ponsive design */ @media (max-width: 768px) { .custom-trust-badges { flex-direction: column; gap: 15px; } .custom-trust-badges .trust-badge { width: 100%; } } </style>'
    ]);
}

function onepaqucpro_sanitize_premium_checkbox($value)
{
    if (!function_exists('onepaqucpro_premium_feature') || !onepaqucpro_premium_feature()) {
        return '0';
    }

    return $value === '1' ? '1' : '0';
}

function onepaqucpro_sanitize_floating_cart_checkout_behavior($value)
{
    $value = sanitize_text_field($value);

    if ($value !== 'popup_checkout') {
        return 'direct_checkout';
    }

    return function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature() ? 'popup_checkout' : 'direct_checkout';
}

function onepaqucpro_sanitize_floating_cart_empty_icon($value)
{
    if (!function_exists('onepaqucpro_premium_feature') || !onepaqucpro_premium_feature()) {
        return 'cart';
    }

    return function_exists('onepaqucpro_sanitize_floating_cart_empty_icon_key')
        ? onepaqucpro_sanitize_floating_cart_empty_icon_key($value)
        : 'cart';
}

function onepaqucpro_sanitize_premium_text_field($value)
{
    if (!function_exists('onepaqucpro_premium_feature') || !onepaqucpro_premium_feature()) {
        return '';
    }

    return sanitize_text_field($value);
}

function onepaqucpro_sanitize_floating_cart_meta_rules($value)
{
    if (!function_exists('onepaqucpro_premium_feature') || !onepaqucpro_premium_feature()) {
        return '';
    }

    return onepaqucpro_encode_floating_cart_meta_rules($value);
}

function onepaqucpro_handle_reset_settings()
{
    // verify nonce
    if (!isset($_POST['onepaquc_reset_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['onepaquc_reset_settings_nonce'])), 'onepaquc_reset_settings')) {
        return;
    }

    if (isset($_POST['onepaqucpro_reset_settings']) && $_POST['onepaqucpro_reset_settings'] == '1') {
        global $onepaqucpro_string_settings_fields;
        foreach (onepaqucpro_rmenupro_fields() as $key => $field) {
            delete_option($key);
        }
        foreach (onepaqucpro_onpcheckout_heading() as $key => $field) {
            delete_option($key);
        }

        foreach ($onepaqucpro_string_settings_fields as $field) {
            delete_option($field);
        }

        global $onepaqucpro_checkoutformfields, $onepaqucpro_productpageformfields;
        $settings = array_merge(array_keys($onepaqucpro_checkoutformfields), array_keys($onepaqucpro_productpageformfields));

        foreach ($settings as $setting) {
            delete_option($setting);
        }

        // List of settings to reset
        $settings_to_reset = [
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

        // Reset each setting
        foreach ($settings_to_reset as $setting) {
            delete_option($setting);
        }

        // Redirect to the same page to avoid resubmission
        $current_url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        // Check if the URL already has a query string
        if (strpos($current_url, '?') !== false) {
            // Append the new action parameter
            $redirect_url = $current_url . '&action=reset_success';
        } else {
            // Add the action parameter as the first query parameter
            $redirect_url = $current_url . '?action=reset_success';
        }
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_init', 'onepaqucpro_handle_reset_settings');

function onepaqucpro_sanitize_trust_badges_items($items)
{
    // Only accept an array
    if (!is_array($items)) {
        return [];
    }

    // Remove empty items and sanitize
    $sanitized = [];
    foreach ($items as $item) {
        // Only accept arrays with at least 'icon' or 'text'
        if (!is_array($item)) {
            continue;
        }
        // Remove empty trust badge rows (all fields empty)
        $has_content = false;
        foreach ($item as $value) {
            if (trim($value) !== '') {
                $has_content = true;
                break;
            }
        }
        if (!$has_content) {
            continue;
        }
        // Remove if text is "New Badge"
        if (isset($item['text']) && trim($item['text']) === 'New Badge {{index}}') {
            continue;
        }
        // Sanitize each field
        $sanitized_item = [];
        foreach ($item as $key => $value) {
            $sanitized_item[$key] = sanitize_text_field($value);
        }
        $sanitized[] = $sanitized_item;
    }

    // Remove duplicates (same icon and text)
    $unique = [];
    foreach ($sanitized as $item) {
        $hash = md5($item['icon'] . '|' . $item['text']);
        $unique[$hash] = $item;
    }

    // Re-index array
    return array_values($unique);
}

function onepaqucpro_sanitize_array_of_text($value)
{
    if (!is_array($value)) {
        return [];
    }

    return array_map('sanitize_text_field', $value);
}


function onepaqucpro_cart_custom_css()
{
    global $onepaqucpro_rcheckoutformfields;

    // Initialize an empty string for the custom CSS
    $custom_css = '';

    // Loop through the fields to generate CSS
    foreach (onepaqucpro_rmenupro_fields() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    foreach (onepaqucpro_onpcheckout_heading() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    if (get_option('onepaqucpro_checkout_fields')) {
        $checkout_fields = get_option('onepaqucpro_checkout_fields');
        foreach ($checkout_fields as $field) {
            if (isset($onepaqucpro_rcheckoutformfields[$field])) {
                $selector = $onepaqucpro_rcheckoutformfields[$field]['selector'];
                $custom_css .= "{$selector} { display: none !important; }\n";
            }
        }
    }

    // Add the inline styles
    wp_add_inline_style('rmenupro-cart-style', esc_html($custom_css));
}

// Hook to enqueue the styles
add_action('wp_enqueue_scripts', 'onepaqucpro_cart_custom_css', 9999999);

function onepaqucpro_rmenupro_fields()
{
    return [
        'hide_coupon_toggle'          => ['selector' => '#checkout-form .woocommerce-form-coupon-toggle, #checkout-form .col-form-coupon,.one-page-checkout-container .woocommerce-form-coupon-toggle, .one-page-checkout-container .col-form-coupon', 'title' => 'Hide Top Coupon'],
        'hide_customer_details_col2'  => ['selector' => '.checkout-popup .woocommerce-shipping-fields, .one-page-checkout-container .woocommerce-shipping-fields', 'title' => 'Hide Shipping Address'],
        'hide_privacy_policy_text'    => ['selector' => '#checkout-form .woocommerce-privacy-policy-text,.one-page-checkout-container .woocommerce-privacy-policy-text', 'title' => 'Hide Privacy Policy Text'],
        'hide_payment'                 => ['selector' => '#checkout-form div#payment ul,.one-page-checkout-container div#payment ul', 'title' => 'Hide Payment Options'],
        'hide_product'                 => ['selector' => '#checkout-form table.shop_table,.one-page-checkout-container table.shop_table', 'title' => 'Hide Product Table']

    ];
}

function onepaqucpro_onpcheckout_heading()
{
    return [
        'hide_billing_details'          => ['selector' => '#checkout-form .woocommerce-billing-fields h3,.one-page-checkout-container .woocommerce-billing-fields h3', 'title' => 'Hide Billing details'],
        'hide_additional_details'          => ['selector' => '.checkout-popup .woocommerce-additional-fields h3,.one-page-checkout-container .woocommerce-additional-fields h3', 'title' => 'Hide Additional Details'],
        'hide_order_review_heading'   => ['selector' => '#checkout-form h3#order_review_heading,.one-page-checkout-container h3#order_review_heading', 'title' => 'Hide Order Review Heading'],
    ];
}
