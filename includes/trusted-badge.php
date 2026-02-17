<?php

/**
 * Trust Badges Settings for One Page Quick Checkout
 */

// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display Trust Badges settings content
 */
function onepaqucpro_trust_badges_settings_content()
{
    // Get saved badges or load defaults
    $badges = get_option('onepaqucpro_my_trust_badges_items', array(
        array(
            'icon' => 'dashicons-lock',
            'text' => 'Secure Payment',
            'enabled' => 1
        ),
        array(
            'icon' => 'dashicons-shield',
            'text' => '30-Day Money Back',
            'enabled' => 1
        ),
        array(
            'icon' => 'dashicons-privacy',
            'text' => 'Privacy Protected',
            'enabled' => 1
        )
    ));
    $show_custom_html = '1' === (string) get_option('show_custom_html', '0');

    // Get available dashicons for selection
    $dashicons = array(
        'dashicons-lock' => esc_html__('Lock', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-shield' => esc_html__('Shield', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-privacy' => esc_html__('Privacy', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-cart' => esc_html__('Cart', 'one-page-quick-checkout-for-woocommerce-pro'),
        // 'dashicons-credit-card' => esc_html__('Credit Card', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-yes' => esc_html__('Checkmark', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-thumbs-up' => esc_html__('Thumbs Up', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-awards' => esc_html__('Award', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-star-filled' => esc_html__('Star', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-businessman' => esc_html__('Customer', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-calculator' => esc_html__('Calculator', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-clock' => esc_html__('Clock', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-phone' => esc_html__('Phone', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-email' => esc_html__('Email', 'one-page-quick-checkout-for-woocommerce-pro'),
        'dashicons-admin-site' => esc_html__('Website', 'one-page-quick-checkout-for-woocommerce-pro'),
    );

    $onepaquc_helper = new onepaqucpro_helper();
?>
    <div class="trust-badges-settings-wrapper">
        <?php $onepaquc_helper->sec_head('h3','plugincy_sec_head','<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" xml:space="preserve" width="16" height="16"><path d="m3.513 3.493.039-.007.007-.039c.085-.492.368-.925.778-1.208L2.171.073a.252.252 0 0 0-.43.177l-.013 1.532-1.454-.026a.252.252 0 0 0-.184.43l2.146 2.146.007-.013a1.86 1.86 0 0 1 1.27-.827M15.91 13.809l-2.146-2.146-.007.013a1.85 1.85 0 0 1-1.27.83l-.039.007-.007.039a1.85 1.85 0 0 1-.778 1.208l2.166 2.166c.158.158.43.046.43-.177l.013-1.532 1.454.026c.226.003.341-.272.184-.433m-3.596-2.389a.75.75 0 0 0 .604-.942l-.082-.282a.745.745 0 0 1 .295-.82l.243-.167a.747.747 0 0 0 .135-1.112l-.197-.22a.75.75 0 0 1-.092-.866l.144-.256a.748.748 0 0 0-.361-1.06l-.272-.112a.75.75 0 0 1-.459-.742l.02-.292a.747.747 0 0 0-.784-.797l-.292.016a.75.75 0 0 1-.735-.469l-.108-.272a.746.746 0 0 0-1.053-.377l-.256.141a.75.75 0 0 1-.866-.105l-.217-.2a.743.743 0 0 0-1.112.118l-.171.24a.745.745 0 0 1-.824.282l-.282-.085a.745.745 0 0 0-.952.587l-.049.289a.75.75 0 0 1-.62.61l-.289.046a.75.75 0 0 0-.604.942l.082.282a.745.745 0 0 1-.295.82l-.243.167a.747.747 0 0 0-.135 1.112l.197.22a.75.75 0 0 1 .092.866l-.144.256a.748.748 0 0 0 .361 1.06l.272.112c.295.125.479.42.459.742l-.02.292a.747.747 0 0 0 .784.797l.292-.016a.75.75 0 0 1 .735.469l.108.272a.746.746 0 0 0 1.053.377l.256-.141a.75.75 0 0 1 .866.105l.217.2a.743.743 0 0 0 1.112-.118l.171-.24a.745.745 0 0 1 .824-.282l.282.085a.745.745 0 0 0 .952-.587l.049-.289a.75.75 0 0 1 .62-.61zm-1.815-.922c-1.381 1.381-3.623 1.381-5.004 0s-1.381-3.623 0-5.004 3.623-1.381 5.004 0a3.54 3.54 0 0 1 0 5.004"/><path d="m8.849 6.922-.561-1.375a.312.312 0 0 0-.578 0l-.561 1.375-1.48.108a.31.31 0 0 0-.177.548l1.135.958-.354 1.441a.31.31 0 0 0 .466.338l1.26-.784 1.26.784a.31.31 0 0 0 .466-.338l-.354-1.441 1.135-.958a.31.31 0 0 0-.177-.548z"/></svg>',esc_html__('Trust Badges Configuration', 'one-page-quick-checkout-for-woocommerce')); ?>
        
        <table class="form-table <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Enable Trust Badges', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                <td>
                    <label class="switch">
                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" name="<?php echo !onepaqucpro_premium_feature() ? 'pro_trust_badges_enabled' : 'onepaqucpro_trust_badges_enabled'; ?>" value="1"
                            <?php checked(1, get_option('onepaqucpro_trust_badges_enabled', 0), true); ?> />
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php esc_html_e('Display trust signals and security badges on the checkout page.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e('Badge Position', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                <td>
                    <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_trust_badge_position' : 'onepaqucpro_trust_badge_position'; ?>">
                        <option value="above_checkout" <?php selected(get_option('onepaqucpro_trust_badge_position', 'below_checkout'), 'above_checkout'); ?>><?php esc_html_e('Above Checkout Form', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <option value="below_checkout" <?php selected(get_option('onepaqucpro_trust_badge_position', 'below_checkout'), 'below_checkout'); ?>><?php esc_html_e('Below Checkout Form', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <option value="payment_section" <?php selected(get_option('onepaqucpro_trust_badge_position', 'below_checkout'), 'payment_section'); ?>><?php esc_html_e('Next to Payment Methods', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <!-- <option value="order_summary" <?php //selected(get_option('onepaqucpro_trust_badge_position', 'below_checkout'), 'order_summary'); ?>><?php //esc_html_e('Below Order Summary (Coming Soon)', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option> -->
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e('Badge Style', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                <td>
                    <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="<?php echo !onepaqucpro_premium_feature() ? 'pro_trust_badge_style' : 'onepaqucpro_trust_badge_style'; ?>">
                        <option value="horizontal" <?php selected(get_option('onepaqucpro_trust_badge_style', 'horizontal'), 'horizontal'); ?>><?php esc_html_e('Horizontal Row', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <option value="grid" <?php selected(get_option('onepaqucpro_trust_badge_style', 'horizontal'), 'grid'); ?>><?php esc_html_e('Grid (2 columns)', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <option value="vertical" <?php selected(get_option('onepaqucpro_trust_badge_style', 'horizontal'), 'vertical'); ?>><?php esc_html_e('Vertical List', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Trust Badge Items', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                <td>
                    <div class="trust-badges-container">
                        <div class="badge-items-wrapper">
                            <?php foreach ($badges as $index => $badge) : ?>
                                <div class="badge-item" data-index="<?php echo esc_attr($index); ?>">
                                    <div class="badge-header">
                                        <span class="badge-title"><?php echo esc_html($badge['text']); ?></span>
                                        <span class="badge-controls">
                                            <a href="#" class="badge-toggle"><?php echo !empty($badge['enabled']) ? '👁️' : '👁️‍🗨️'; ?></a>
                                            <a href="#" class="badge-remove">❌</a>
                                        </span>
                                    </div>
                                    <div class="badge-content">
                                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="hidden" name="onepaqucpro_my_trust_badges_items[<?php echo esc_attr($index); ?>][enabled]"
                                            value="<?php echo !empty($badge['enabled']) ? '1' : '0'; ?>" />

                                        <p>
                                            <label><?php esc_html_e('Icon:', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
                                            <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="onepaqucpro_my_trust_badges_items[<?php echo esc_attr($index); ?>][icon]" class="badge-icon-select">
                                                <?php foreach ($dashicons as $icon => $name) : ?>
                                                    <option value="<?php echo esc_attr($icon); ?>" <?php selected($badge['icon'], $icon); ?>>
                                                        <?php echo esc_html($name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="preview-icon">
                                                <i class="dashicons <?php echo esc_attr($badge['icon']); ?>"></i>
                                            </span>
                                        </p>

                                        <p>
                                            <label><?php esc_html_e('Text:', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
                                            <input type="text" name="onepaqucpro_my_trust_badges_items[<?php echo esc_attr($index); ?>][text]"
                                                value="<?php echo esc_attr($badge['text']); ?>" class="regular-text" />
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="button" class="button button-secondary add-new-badge">
                            <?php esc_html_e('Add New Badge', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <tr valign="top" class="custom-html-section">
                <th scope="row"><?php esc_html_e('Advanced: Custom HTML', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                <td>
                    <p>
                        <label>
                            <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="checkbox" id="show-custom-html" name="show_custom_html" value="1"
                                <?php checked(true, $show_custom_html, true); ?> />
                            <?php esc_html_e('I want to use custom HTML instead', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                        </label>
                    </p>

                    <div id="custom-html-editor" style="<?php echo $show_custom_html ? '' : 'display:none;'; ?>">
                        <textarea <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="onepaqucpro_trust_badge_custom_html" rows="6" class="large-text code"><?php echo esc_textarea(get_option('onepaqucpro_trust_badge_custom_html', '')); ?></textarea>
                        <p class="description"><?php esc_html_e('Custom HTML for trust badges. You can use dashicons or include your own images.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                </td>
            </tr>
        </table>

        <div id="badge-template" style="display:none;">
            <div class="badge-item" data-index="{{index}}">
                <div class="badge-header">
                    <span class="badge-title"><?php esc_html_e('New Badge', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <span class="badge-controls">
                        <a href="#" class="badge-toggle">👁️</a>
                        <a href="#" class="badge-remove">❌</a>
                    </span>
                </div>
                <div class="badge-content">
                    <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="hidden" name="onepaqucpro_my_trust_badges_items[{{index}}][enabled]" value="1" />

                    <p>
                        <label><?php esc_html_e('Icon:', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
                        <select <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> name="onepaqucpro_my_trust_badges_items[{{index}}][icon]" class="badge-icon-select">
                            <?php foreach ($dashicons as $icon => $name) : ?>
                                <option value="<?php echo esc_attr($icon); ?>">
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="preview-icon">
                            <i class="dashicons dashicons-shield"></i>
                        </span>
                    </p>

                    <p>
                        <label><?php esc_html_e('Text:', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
                        <input <?php echo !onepaqucpro_premium_feature() ? 'disabled' : ''; ?> type="text" name="onepaqucpro_my_trust_badges_items[{{index}}][text]"
                            value="<?php esc_html_e('New Badge {{index}}', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" class="regular-text" />
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .trust-badges-container {
            margin-bottom: 20px;
        }

        .badge-items-wrapper {
            margin-bottom: 15px;
        }

        .badge-item {
            border: 1px solid #ddd;
            background: #f9f9f9;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        .badge-header {
            padding: 8px 12px;
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }

        .badge-content {
            padding: 12px;
        }

        .badge-controls a {
            margin-left: 10px;
            text-decoration: none;
        }

        .preview-icon {
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }

        .preview-icon .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {


            // Toggle custom HTML editor visibility
            $('#show-custom-html').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.badge-items-wrapper').closest('tr').hide();
                    $('#custom-html-editor').slideDown();
                } else {
                    $('#custom-html-editor').slideUp();
                    $('.badge-items-wrapper').closest('tr').show();
                }
            });

            // initialilly trigger on change to set visibility
            $('#show-custom-html').trigger('change');

            // if onepaqucpro_trust_badges_enabled is not checked, disable all fields use on change
            $('input[name="onepaqucpro_trust_badges_enabled"]').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('div#tab-6 table:first').find('input, select, textarea').not('[name="onepaqucpro_trust_badges_enabled"]').toggleClass("disabled",!isChecked);
            }).trigger('change');

            // Toggle badge content
            $('.badge-items-wrapper').on('click', '.badge-header', function(e) {
                if (!$(e.target).hasClass('badge-toggle') && !$(e.target).hasClass('badge-remove')) {
                    $(this).next('.badge-content').slideToggle();
                }
            });

            // Toggle badge visibility
            $('.badge-items-wrapper').on('click', '.badge-toggle', function(e) {
                e.preventDefault();

                var item = $(this).closest('.badge-item');
                var enabledField = item.find('input[name*="[enabled]"]');

                if (enabledField.val() === '1') {
                    enabledField.val('0');
                    $(this).text('👁️‍🗨️');
                } else {
                    enabledField.val('1');
                    $(this).text('👁️');
                }
            });

            // Remove badge
            $('.badge-items-wrapper').on('click', '.badge-remove', function(e) {
                e.preventDefault();
                $(this).closest('.badge-item').remove();
            });

            // Add new badge
            $('.add-new-badge').on('click', function() {
                var template = $('#badge-template').html();
                var index = $('.badge-item').length;

                // Replace placeholder index with actual index
                template = template.replace(/{{index}}/g, index);

                $('.badge-items-wrapper').append(template);
            });

            // Live preview icon selection
            $('.badge-items-wrapper').on('change', '.badge-icon-select', function() {
                var iconClass = $(this).val();
                $(this).next('.preview-icon').html('<i class="dashicons ' + iconClass + '"></i>');
            });

            // Toggle custom HTML section
            $('#show-custom-html').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#custom-html-editor').slideDown();
                } else {
                    $('#custom-html-editor').slideUp();
                }
            });
        });
    </script>
<?php
}

/**
 * Build trust badges HTML for frontend output.
 */
function onepaqucpro_get_trust_badges_html()
{
    // Check if trust badges are enabled
    if (!get_option('onepaqucpro_trust_badges_enabled', 0)) {
        return '';
    }

    // Only use custom HTML when explicitly enabled.
    $show_custom_html = '1' === (string) get_option('show_custom_html', '0');
    $custom_html = get_option('onepaqucpro_trust_badge_custom_html', '');
    if ($show_custom_html && !empty($custom_html)) {
        return (string) $custom_html;
    }

    // Otherwise, build the badges from settings
    $badges = get_option('onepaqucpro_my_trust_badges_items', array());
    $style = get_option('onepaqucpro_trust_badge_style', 'horizontal');

    if (empty($badges) || !is_array($badges)) {
        return '';
    }

    $html = '<div class="onepaquc-trust-badges style-' . esc_attr($style) . '">';

    foreach ($badges as $badge) {
        if (empty($badge['enabled'])) {
            continue;
        }

        $html .= '<div class="trust-badge">';
        if (!empty($badge['icon'])) {
            $html .= '<i class="dashicons ' . esc_attr($badge['icon']) . '"></i>';
        }
        if (!empty($badge['text'])) {
            $html .= '<span>' . esc_html($badge['text']) . '</span>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Render trust badges on frontend
 */
function onepaqucpro_display_trust_badges()
{
    $html = onepaqucpro_get_trust_badges_html();
    if ('' === trim($html)) {
        return;
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Custom HTML is intentionally allowed for this feature.
    echo $html;
}

/**
 * Add trust badges to checkout page
 */
function onepaqucpro_add_trust_badges_to_checkout()
{
    $position = get_option('onepaqucpro_trust_badge_position', 'below_checkout');

    switch ($position) {
        case 'above_checkout':
            add_action('woocommerce_before_checkout_form', 'onepaqucpro_display_trust_badges', 10);
            break;
        case 'below_checkout':
            add_action('woocommerce_after_checkout_form', 'onepaqucpro_display_trust_badges', 10);
            break;
        case 'payment_section':
            add_action('woocommerce_review_order_after_payment', 'onepaqucpro_display_trust_badges', 10);
            break;
        case 'order_summary':
            add_action('woocommerce_checkout_after_order_review', 'onepaqucpro_display_trust_badges', 10);
            break;
    }
}
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    add_action('init', 'onepaqucpro_add_trust_badges_to_checkout');
}

/**
 * Add trust badges to WooCommerce Checkout Block output.
 *
 * Note: Blocks do not trigger classic checkout hooks like
 * woocommerce_before_checkout_form / woocommerce_after_checkout_form.
 */
function onepaqucpro_add_trust_badges_to_checkout_block($block_content, $block)
{
    if (is_admin() && !wp_doing_ajax()) {
        return $block_content;
    }

    if (!is_array($block) || empty($block['blockName']) || 'woocommerce/checkout' !== $block['blockName']) {
        return $block_content;
    }

    if (false !== strpos($block_content, 'onepaquc-trust-badges-block-hook')) {
        return $block_content;
    }

    $badges_html = onepaqucpro_get_trust_badges_html();
    if ('' === trim($badges_html)) {
        return $block_content;
    }

    $position = get_option('onepaqucpro_trust_badge_position', 'below_checkout');
    $wrapped_badges_html = '<div class="onepaquc-trust-badges-block-hook">' . $badges_html . '</div>';

    if ('above_checkout' === $position) {
        return $wrapped_badges_html . $block_content;
    }

    if ('below_checkout' === $position) {
        return $block_content . $wrapped_badges_html;
    }

    if ('payment_section' === $position) {
        static $source_index = 0;
        $source_index++;

        // Keep source outside payment block and mount it into the payment section via JS.
        $source_html = '<div class="onepaquc-trust-badges-payment-source" data-onepaquc-trust-badges-payment-source="1" data-onepaquc-source-id="' . esc_attr((string) $source_index) . '" style="display:none;">' . $badges_html . '</div>';
        return $block_content . $source_html;
    }

    // order_summary is not exposed as a stable server-side hook point in Checkout Blocks.
    return $block_content . $wrapped_badges_html;
}
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    add_filter('render_block_woocommerce/checkout', 'onepaqucpro_add_trust_badges_to_checkout_block', 20, 2);
}

/**
 * Move trust badges into the payment section for Checkout Blocks.
 */
function onepaqucpro_trust_badges_blocks_payment_section_script()
{
    if (is_admin()) {
        return;
    }

    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }

    if (!get_option('onepaqucpro_trust_badges_enabled', 0)) {
        return;
    }

    if ('payment_section' !== get_option('onepaqucpro_trust_badge_position', 'below_checkout')) {
        return;
    }
?>
    <script>
        (function() {
            function findPaymentTarget(root) {
                if (!root) {
                    return null;
                }

                var selectors = [
                    '.wc-block-components-checkout-step--payment-methods .wc-block-components-checkout-step__container',
                    '.wc-block-components-checkout-step--payment .wc-block-components-checkout-step__container',
                    '.wc-block-components-checkout-step--payment-methods',
                    '.wc-block-components-checkout-step--payment',
                    '.wc-block-components-checkout-payment-methods',
                    '.wc-block-components-payment-methods',
                    '.wc-block-checkout__payment-methods',
                    '.wp-block-woocommerce-checkout-payment-block'
                ];

                for (var i = 0; i < selectors.length; i++) {
                    var target = root.querySelector(selectors[i]);
                    if (target) {
                        return target;
                    }
                }

                return null;
            }

            function createOrGetMount(root, sourceId, sourceHtml) {
                if (!root) {
                    return null;
                }

                var selector = '.onepaquc-trust-badges-block-hook--payment-section[data-onepaquc-source-id="' + sourceId + '"]';
                var mount = root.querySelector(selector);

                if (!mount) {
                    mount = document.createElement('div');
                    mount.className = 'onepaquc-trust-badges-block-hook onepaquc-trust-badges-block-hook--payment-section';
                    mount.setAttribute('data-onepaquc-source-id', sourceId);
                    mount.innerHTML = sourceHtml;
                }

                return mount;
            }

            function mountSources() {
                var sources = document.querySelectorAll('[data-onepaquc-trust-badges-payment-source="1"]');
                var mountedAny = false;

                for (var i = 0; i < sources.length; i++) {
                    var source = sources[i];
                    var sourceId = source.getAttribute('data-onepaquc-source-id') || String(i + 1);

                    var checkoutRoot = source.previousElementSibling;
                    if (!checkoutRoot || !checkoutRoot.matches('.wp-block-woocommerce-checkout, .wc-block-checkout, .wc-block-components-checkout')) {
                        checkoutRoot = document.querySelector('.wp-block-woocommerce-checkout, .wc-block-checkout, .wc-block-components-checkout');
                    }

                    if (!checkoutRoot) {
                        continue;
                    }

                    var paymentTarget = findPaymentTarget(checkoutRoot);
                    if (!paymentTarget) {
                        continue;
                    }

                    var mount = createOrGetMount(checkoutRoot, sourceId, source.innerHTML);
                    if (!mount) {
                        continue;
                    }

                    if (mount.parentNode !== paymentTarget) {
                        paymentTarget.appendChild(mount);
                    }

                    source.setAttribute('data-onepaquc-mounted', '1');
                    mountedAny = true;
                }

                return mountedAny;
            }

            function fallbackBelowCheckout() {
                var sources = document.querySelectorAll('[data-onepaquc-trust-badges-payment-source="1"]');
                for (var i = 0; i < sources.length; i++) {
                    var source = sources[i];
                    if ('1' === source.getAttribute('data-onepaquc-mounted')) {
                        continue;
                    }

                    var checkoutRoot = source.previousElementSibling;
                    if (!checkoutRoot || !checkoutRoot.matches('.wp-block-woocommerce-checkout, .wc-block-checkout, .wc-block-components-checkout')) {
                        continue;
                    }

                    var sourceId = source.getAttribute('data-onepaquc-source-id') || String(i + 1);
                    var mount = document.createElement('div');
                    mount.className = 'onepaquc-trust-badges-block-hook onepaquc-trust-badges-block-hook--payment-fallback';
                    mount.setAttribute('data-onepaquc-source-id', sourceId);
                    mount.innerHTML = source.innerHTML;
                    checkoutRoot.parentNode.insertBefore(mount, checkoutRoot.nextSibling);
                    source.setAttribute('data-onepaquc-mounted', '1');
                }
            }

            function init() {
                mountSources();

                var observer = new MutationObserver(function() {
                    mountSources();
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                window.setTimeout(function() {
                    observer.disconnect();
                    if (!mountSources()) {
                        fallbackBelowCheckout();
                    }
                }, 12000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
<?php
}
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    add_action('wp_footer', 'onepaqucpro_trust_badges_blocks_payment_section_script', 99);
}

/**
 * Add frontend styles for trust badges
 */
function onepaqucpro_trust_badges_styles()
{
    if (!get_option('onepaqucpro_trust_badges_enabled', 0)) {
        return;
    }

    $style = get_option('onepaqucpro_trust_badge_style', 'horizontal');

    // Get primary color from theme or use a default
    $primary_color = '#3498db'; // Default blue

    // Try to get theme color if available
    if (function_exists('get_theme_mod')) {
        $theme_color = get_theme_mod('primary_color', '');
        if (!empty($theme_color)) {
            $primary_color = $theme_color;
        }
    }

?>
    <style type="text/css">
        .onepaquc-trust-badges {
            margin: 25px 0;
            display: flex;
            flex-wrap: wrap;
            <?php if ($style == 'horizontal'): ?>flex-direction: row;
            justify-content: space-evenly;
            align-items: stretch;
            <?php elseif ($style == 'vertical'): ?>flex-direction: column;
            <?php elseif ($style == 'grid'): ?>display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 15px;
            <?php endif; ?>
        }

        .onepaquc-trust-badges .trust-badge {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 6px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 3px solid <?php echo esc_attr($primary_color); ?>;
        }

        .onepaquc-trust-badges .trust-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .onepaquc-trust-badges .trust-badge .dashicons {
            font-size: 22px;
            width: 22px;
            height: 22px;
            margin-right: 12px;
            color: <?php echo esc_attr($primary_color); ?>;
            padding: 8px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .onepaquc-trust-badges .trust-badge span {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .onepaquc-trust-badges.style-horizontal .trust-badge {
            flex: 1;
            justify-content: center;
            text-align: center;
            min-width: 140px;
            flex-direction: column;
            padding: 20px 10px;
        }

        .onepaquc-trust-badges.style-horizontal .trust-badge .dashicons {
            margin-right: 0;
            margin-bottom: 10px;
            font-size: 28px;
            width: 50px;
            height: 50px;
            padding: 12px;
        }

        .onepaquc-trust-badges.style-vertical .trust-badge {
            width: 100%;
            margin: 8px 0;
            border-left: 4px solid <?php echo esc_attr($primary_color); ?>;
        }

        /* Color variations based on icon type */
        .onepaquc-trust-badges .trust-badge .dashicons-lock {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-shield {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-privacy {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-cart {
            background-color: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-credit-card {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-yes,
        .onepaquc-trust-badges .trust-badge .dashicons-thumbs-up {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .onepaquc-trust-badges .trust-badge .dashicons-star-filled,
        .onepaquc-trust-badges .trust-badge .dashicons-awards {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .onepaquc-trust-badges.style-horizontal {
                flex-direction: column;
            }

            .onepaquc-trust-badges.style-grid {
                grid-template-columns: 1fr;
            }

            .onepaquc-trust-badges .trust-badge {
                width: 100%;
                margin: 5px 0;
                justify-content: flex-start;
                flex-direction: row;
                padding: 12px;
                text-align: left;
            }

            .onepaquc-trust-badges.style-horizontal .trust-badge .dashicons {
                margin-right: 10px;
                margin-bottom: 0;
            }
        }
    </style>
<?php
}
if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
    add_action('wp_head', 'onepaqucpro_trust_badges_styles');
}
