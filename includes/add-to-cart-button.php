<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Custom function to apply the selected Add to Cart button styles
 */


$iscustomaddtocart = get_option('rmenupro_enable_custom_add_to_cart', 0);

if ($iscustomaddtocart) {
    add_action('wp_head', 'onepaqucpro_apply_add_to_cart_styles');
    add_action('admin_footer', 'onepaqucpro_add_to_cart_admin_script');

    // Initialize the handler
    new ONEPAQUCPRO_Add_To_Cart_Handler();
}
function onepaqucpro_apply_add_to_cart_styles()
{
    // Get saved options with defaults
    $button_style = get_option('rmenupro_add_to_cart_style', 'default');
    $bg_color = get_option('rmenupro_add_to_cart_bg_color', '#000');
    $text_color = get_option('rmenupro_add_to_cart_text_color', '#ffffff');
    $hover_bg_color = get_option('rmenupro_add_to_cart_hover_bg_color', '#7f4579');
    $hover_text_color = get_option('rmenupro_add_to_cart_hover_text_color', '#ffffff');
    $border_radius = get_option('rmenupro_add_to_cart_border_radius', '3');
    $font_size = get_option('rmenupro_add_to_cart_font_size', '14');
    $button_width = get_option('rmenupro_add_to_cart_width', 'auto');
    $custom_width = get_option('rmenupro_add_to_cart_custom_width', '150');
    $button_icon = get_option('rmenupro_add_to_cart_icon', 'none');
    $icon_position = get_option('rmenupro_add_to_cart_icon_position', 'left');
    $custom_css = get_option('rmenupro_add_to_cart_custom_css', '');

    // Mobile-specific settings
    $sticky_mobile = !onepaqucpro_premium_feature() ? 0 : get_option('rmenupro_sticky_add_to_cart_mobile', 0);
    $mobile_text = !onepaqucpro_premium_feature() ? '' : get_option('rmenupro_mobile_add_to_cart_text', '');
    $mobile_button_size = !onepaqucpro_premium_feature() ? 'default' : get_option('rmenupro_mobile_button_size', 'default');
    $mobile_icon_only = !onepaqucpro_premium_feature() ? 0 : get_option('rmenupro_mobile_icon_only', 0);

    // Advanced options
    $loading_effect = !onepaqucpro_premium_feature() ? 'spinner' : get_option('rmenupro_add_to_cart_loading_effect', 'spinner');
    $disable_out_of_stock = !onepaqucpro_premium_feature() ? 1 : get_option('rmenupro_disable_btn_out_of_stock', 1);

    // Compatibility settings
    $force_css = get_option('rmenupro_force_button_css', 0);

    // Add !important rule if force CSS is enabled
    $important = $force_css ? ' !important' : '';

    // Start building CSS
    $css = '';

    // Only apply custom styling if not using default WooCommerce style
    if ($button_style != 'default') {
        // Base button styles
        $css .= '.rmenupro-mobile-sticky-cart .single_add_to_cart_button.button.alt, .rmenupro-mobile-sticky-cart button[name="add-to-cart"], .single .product .single_add_to_cart_button,.woocommerce a.button.add_to_cart_button:not(.product_type_variable), .woocommerce button.button.add_to_cart_button:not(.product_type_variable), .woocommerce input.button.add_to_cart_button:not(.product_type_variable), .woocommerce #respond input#submit, .woocommerce a.button.alt.add_to_cart_button:not(.product_type_variable), .woocommerce button.button.alt.add_to_cart_button:not(.product_type_variable), .woocommerce input.button.alt.add_to_cart_button:not(.product_type_variable), .woocommerce #respond input#submit.alt {';



        // Common styles for all custom button types
        $css .= "background-color: {$bg_color}{$important};";
        $css .= "color: {$text_color}{$important};";
        $css .= "font-size: {$font_size}px{$important};";
        $css .= "border-radius: {$border_radius}px{$important};";

        // Additional padding for top/bottom icon buttons
        if ($button_icon != 'none' && ($icon_position == 'top' || $icon_position == 'bottom')) {
            $css .= "padding-top: 12px{$important};";
            $css .= "padding-bottom: 12px{$important};";
            $css .= "text-align: center{$important};";
        }

        // Width settings
        if ($button_width == 'full') {
            $css .= "width: 100%{$important};";
            $css .= "text-align: center{$important};";
        } elseif ($button_width == 'custom') {
            $css .= "width: {$custom_width}px{$important};";
            $css .= "text-align: center{$important};";
        }

        // Additional styles per button type
        switch ($button_style) {
            case 'modern':
                $css .= "padding: 12px 20px{$important};";
                $css .= "text-transform: uppercase{$important};";
                $css .= "letter-spacing: 1px{$important};";
                $css .= "font-weight: 600{$important};";
                $css .= "transition: all 0.3s ease{$important};";
                $css .= "box-shadow: 0 2px 5px rgba(0,0,0,0.2){$important};";
                break;

            case 'rounded':
                $css .= "border-radius: 30px{$important};";
                $css .= "padding: 10px 25px{$important};";
                $css .= "font-weight: 500{$important};";
                $css .= "transition: all 0.3s ease{$important};";
                break;

            case 'minimal':
                $css .= "background-color: transparent{$important};";
                $css .= "border: 2px solid {$bg_color}{$important};";
                $css .= "color: {$bg_color}{$important};";
                $css .= "padding: 8px 18px{$important};";
                $css .= "font-weight: 500{$important};";
                $css .= "transition: all 0.3s ease{$important};";
                break;

            case 'custom':
                // No additional styles, user can add their own in custom CSS
                break;
        }

        $css .= '}';

        // Hover styles
        $css .= '.woocommerce a.button.add_to_cart_button:not(.product_type_variable):hover, .woocommerce button.button.add_to_cart_button:not(.product_type_variable):hover, .woocommerce input.button.add_to_cart_button:not(.product_type_variable):hover, .woocommerce #respond input#submit:not(.product_type_variable):hover, .woocommerce a.button.alt.add_to_cart_button:not(.product_type_variable):hover, .woocommerce button.button.alt.add_to_cart_button:not(.product_type_variable):hover, .woocommerce input.button.alt.add_to_cart_button:not(.product_type_variable):hover, .woocommerce #respond input#submit.alt:not(.product_type_variable):hover {';

        if ($button_style == 'minimal') {
            $css .= "background-color: {$bg_color}{$important};";
            $css .= "color: {$text_color}{$important};";
        } else {
            $css .= "background-color: {$hover_bg_color}{$important};";
            $css .= "color: {$hover_text_color}{$important};";
        }

        // Button-specific hover effects
        switch ($button_style) {
            case 'modern':
                $css .= "box-shadow: 0 4px 8px rgba(0,0,0,0.3){$important};";
                $css .= "transform: translateY(-2px){$important};";
                break;

            case 'rounded':
                $css .= "transform: scale(1.05){$important};";
                break;
        }

        $css .= '}';

        // Out of stock button styles
        if ($disable_out_of_stock) {
            $css .= '.woocommerce a.button.add_to_cart_button.disabled, .woocommerce button.button.add_to_cart_button.disabled, .woocommerce input.button.add_to_cart_button.disabled, .woocommerce #respond input#submit.disabled {';
            $css .= "opacity: 0.5{$important};";
            $css .= "pointer-events: none{$important};";
            $css .= '}';
        }
    }

    // Mobile-specific styles
    $css .= '@media (max-width: 768px) {';

    // Mobile icon only
    if ($mobile_icon_only && $button_icon != 'none') {
        $css .= '.woocommerce a.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-text, .woocommerce button.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-text, .woocommerce input.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-text, .single_add_to_cart_button .rmenupro-btn-text {';
        $css .= "display: none{$important};";
        $css .= '}';

        $css .= '.woocommerce a.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-icon, .woocommerce button.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-icon, .woocommerce input.button.add_to_cart_button:not(.product_type_variable) .rmenupro-btn-icon, .single_add_to_cart_button .rmenupro-btn-icon {';
        $css .= "margin: 0{$important};";
        $css .= '}';
    }

    // Mobile button size adjustments
    if ($mobile_button_size != 'default') {
        $css .= '.rmenupro-ajax-add-to-cart,.single_add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn),.add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn) {';

        switch ($mobile_button_size) {
            case 'larger':
                $css .= "font-size: " . ($font_size * 1.25) . "px{$important};";
                $css .= "padding: 14px 24px{$important};";
                break;
            case 'smaller':
                $css .= "font-size: " . ($font_size * 0.85) . "px{$important};";
                $css .= "padding: 8px 14px{$important};";
                break;
            case 'full':
                $css .= "width: 100%{$important};";
                $css .= "text-align: center{$important};";
                $css .= "display: block{$important};";
                break;
        }
        $css .= '}';
    }

    // Mobile button text if set
    if (!empty($mobile_text)) {
        $css .= '.rmenupro-ajax-add-to-cart,.single_add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn),.add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn) {';
        $css .= "font-size: 0 !important;";
        $css .= '}';

        $css .= '.rmenupro-ajax-add-to-cart:after,.single_add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):after,.add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):after {';
        $css .= "content: '{$mobile_text}'{$important};";
        $css .= "font-size: {$font_size}px{$important};";
        $css .= "visibility: visible{$important};";
        $css .= '}';
    }

    // Sticky add to cart on mobile
    if ($sticky_mobile) {
        $css .= '.rmenupro-mobile-sticky-cart {';
        $css .= "position: fixed{$important};";
        $css .= "bottom: 0{$important};";
        $css .= "left: 0{$important};";
        $css .= "right: 0{$important};";
        $css .= "z-index: 999999999999999999999999{$important};";
        $css .= "background-color: #ffffff{$important};";
        $css .= "padding: 20px{$important};";
        $css .= "box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1){$important};";
        $css .= '}';

        $css .= '.rmenupro-mobile-sticky-cart .single_add_to_cart_button {';
        $css .= "width: 100%{$important};";
        $css .= "margin: 0{$important};";
        $css .= '}';
    }

    $css .= '}';

    // Loading effect styles
    if ($loading_effect != 'none') {
        $css .= '.woocommerce a.button.add_to_cart_button.loading:not(.product_type_variable), .woocommerce button.button.add_to_cart_button.loading:not(.product_type_variable), .single_add_to_cart_button.loading {';

        switch ($loading_effect) {
            case 'spinner':
                $css .= "position: relative{$important};";
                $css .= "color: transparent{$important};";
                $css .= '}';

                $css .= '.woocommerce a.button.add_to_cart_button.loading:not(.product_type_variable):after, .woocommerce button.button.add_to_cart_button.loading:not(.product_type_variable):after, .single_add_to_cart_button.loading:after {';
                $css .= "content: ''{$important};";
                $css .= "position: absolute{$important};";
                $css .= "top: 50%{$important};";
                $css .= "left: 50%{$important};";
                $css .= "margin: -0.5em 0 0 -0.5em{$important};";
                $css .= "width: 1em{$important};";
                $css .= "height: 1em{$important};";
                $css .= "border-radius: 50%{$important};";
                $css .= "border: 2px solid rgba(255, 255, 255, 0.3){$important};";
                $css .= "border-top-color: {$text_color}{$important};";
                $css .= "animation: rmenupro-spin 0.8s infinite linear{$important};";
                break;

            case 'dots':
                $css .= "position: relative{$important};";
                $css .= "color: transparent{$important};";
                $css .= '}';

                $css .= '.woocommerce a.button.add_to_cart_button.loading:not(.product_type_variable):after, .woocommerce button.button.add_to_cart_button.loading:not(.product_type_variable):after, .single_add_to_cart_button.loading:after {';
                $css .= "content: '...'{$important};";
                $css .= "position: absolute{$important};";
                $css .= "top: 50%{$important};";
                $css .= "left: 50%{$important};";
                $css .= "transform: translate(-50%, -50%){$important};";
                $css .= "color: {$text_color}{$important};";
                $css .= "font-size: 18px{$important};";
                $css .= "letter-spacing: 2px{$important};";
                break;

            case 'pulse':
                $css .= "animation: rmenupro-pulse 1s infinite{$important};";
                break;
        }

        $css .= '}';

        // Add keyframes for animations
        if ($loading_effect == 'spinner') {
            $css .= '@keyframes rmenupro-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }';
        } elseif ($loading_effect == 'pulse') {
            $css .= '@keyframes rmenupro-pulse {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }';
        }
    }

    // Add any custom CSS provided by the user
    if ($button_style == 'custom' && !empty($custom_css)) {
        $css .= $custom_css;
    }

    // Output the inline CSS if there is any
    if (!empty($css)) {
        echo '<style id="rmenupro-add-to-cart-custom-styles">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    // Add Button icon
    if ($button_icon != 'none' && $button_style != 'default') {
        // Add icon with proper positioning through JavaScript
        add_action('wp_footer', 'onepaqucpro_add_icons_to_buttons');
    }

    // Add sticky mobile cart if enabled
    if ($sticky_mobile) {
        add_action('wp_footer', 'rmenupro_add_sticky_mobile_cart');
    }
}


/**
 * No need to enqueue external libraries as we're using inline SVGs
 */

/**
 * Add SVG icons to Add to Cart buttons via JavaScript
 */
function onepaqucpro_add_icons_to_buttons()
{
    $button_icon = get_option('rmenupro_add_to_cart_icon', 'none');
    $icon_position = get_option('rmenupro_add_to_cart_icon_position', 'left');
    $mobile_icon_only = get_option('rmenupro_mobile_icon_only', 0);

    // Map option values to SVG code
    $svg_icon = '';
    switch ($button_icon) {
        case 'cart':
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
            break;
        case 'plus':
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
            break;
        case 'bag':
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>';
            break;
        case 'basket':
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234l-2.546-4.243a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.757a.5.5 0 0 1 .686-.172zM3.394 15l-1.48-6h-.97l1.525 6.426a.75.75 0 0 0 .729.574h9.606a.75.75 0 0 0 .73-.574L15.056 9h-.972l-1.479 6h-9.21z"></path></svg>';
            break;
        default:
            return; // No icon selected
    }

    // Skip if no icon is selected
    if (empty($svg_icon)) {
        return;
    }

?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.add_to_cart_button:not(.product_type_variable), .single_add_to_cart_button:not(.product_type_variable)');
            const svgIcon = `<?php echo $svg_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>`;
            const iconPosition = '<?php echo esc_attr($icon_position); ?>';
            const mobileIconOnly = <?php echo $mobile_icon_only ? 'true' : 'false'; ?>;

            addToCartButtons.forEach(function(button) {
                // Don't modify buttons that already have icons added
                if (button.querySelector('.rmenupro-btn-icon')) {
                    return;
                }

                const buttonText = button.textContent.trim();

                // Create SVG container
                const iconContainer = document.createElement('span');
                iconContainer.className = 'rmenupro-btn-icon';
                iconContainer.innerHTML = svgIcon;

                // Create text container
                const textContainer = document.createElement('span');
                textContainer.className = 'rmenupro-btn-text';
                textContainer.textContent = buttonText;

                // Clear the button's text content
                button.textContent = '';

                // Apply spacing and layout based on icon position
                if (iconPosition === 'left' || iconPosition === 'right') {
                    // Horizontal layout
                    button.style.display = 'inline-flex';
                    button.style.alignItems = 'center';
                    button.style.justifyContent = 'center';

                    if (iconPosition === 'left') {
                        iconContainer.style.marginRight = '8px';
                        button.appendChild(iconContainer);
                        button.appendChild(textContainer);
                    } else {
                        textContainer.style.marginRight = '8px';
                        button.appendChild(textContainer);
                        button.appendChild(iconContainer);
                    }
                } else {
                    // Vertical layout
                    button.style.display = 'inline-flex';
                    button.style.flexDirection = 'column';
                    button.style.alignItems = 'center';
                    button.style.justifyContent = 'center';

                    if (iconPosition === 'top') {
                        iconContainer.style.marginBottom = '5px';
                        button.appendChild(iconContainer);
                        button.appendChild(textContainer);
                    } else {
                        textContainer.style.marginBottom = '5px';
                        button.appendChild(textContainer);
                        button.appendChild(iconContainer);
                    }

                    // For vertical layouts, we might need to adjust padding
                    button.style.paddingTop = '10px';
                    button.style.paddingBottom = '10px';
                }

                // Style the SVG to match text
                const svg = iconContainer.querySelector('svg');
                if (svg) {
                    svg.style.display = 'block';
                }

                // Handle mobile icon only feature if the screen width is less than 768px
                if (mobileIconOnly) {
                    const mediaQuery = window.matchMedia('(max-width: 768px)');
                    const handleMobileView = (e) => {
                        if (e.matches) {
                            // Mobile view - hide text
                            textContainer.style.display = 'none';
                            iconContainer.style.margin = '0';
                        } else {
                            // Desktop view - show text
                            textContainer.style.display = '';
                            iconContainer.style.margin = iconPosition === 'left' ? '0 8px 0 0' :
                                iconPosition === 'right' ? '0 0 0 8px' :
                                iconPosition === 'top' ? '0 0 5px 0' : '5px 0 0 0';
                        }
                    };

                    // Initial check
                    handleMobileView(mediaQuery);

                    // Add listener for screen size changes
                    mediaQuery.addListener(handleMobileView);
                }
            });
        });
    </script>
<?php
}


/**
 * Add sticky add to cart button on mobile
 */
function rmenupro_add_sticky_mobile_cart()
{
    // Only run on product pages
    if (!is_product()) {
        return;
    }

    $mobile_text = get_option('rmenupro_mobile_add_to_cart_text', '');

?>
    <style>
        .rmenupro-mobile-sticky-cart {
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .rmenupro-mobile-sticky-cart .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e5e5;
        }

        .rmenupro-mobile-sticky-cart .product-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            flex: 1;
        }

        .rmenupro-mobile-sticky-cart .quantity {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .rmenupro-mobile-sticky-cart .quantity-label,
        .rmenupro-mobile-sticky-cart .screen-reader-text {
            display: none;
        }

        .rmenupro-mobile-sticky-cart .quantity:before {
            content: "Quantity";
            font-size: 14px;
            font-weight: 500;
            color: #666;
            min-width: 60px;
            clip: auto;
            clip-path: none;
            height: auto;
            width: auto;
            position: relative;
        }

        .rmenupro-mobile-sticky-cart input.qty,
        .rmenupro-mobile-sticky-cart input.input-text.qty {
            width: 60px;
            height: 42px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            -moz-appearance: textfield;
        }

        .rmenupro-mobile-sticky-cart input.qty::-webkit-outer-spin-button,
        .rmenupro-mobile-sticky-cart input.qty::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .rmenupro-mobile-sticky-cart input.qty:focus {
            outline: none;
            border-color: #333;
        }

        .rmenupro-mobile-sticky-cart .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .rmenupro-mobile-sticky-cart button,
        .rmenupro-mobile-sticky-cart .button,
        .rmenupro-mobile-sticky-cart a.button {
            height: 52px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            margin: 0;
        }

        .rmenupro-mobile-sticky-cart button:active,
        .rmenupro-mobile-sticky-cart .button:active {
            transform: scale(0.98);
        }

        .rmenupro-mobile-sticky-cart .single_add_to_cart_button.button.alt,
        .rmenupro-mobile-sticky-cart button[name="add-to-cart"] {
            grid-column: 1;
            width: 100% !important;
        }
        .rmenupro-mobile-sticky-cart .rmenupro-ajax-add-to-cart:after, .rmenupro-mobile-sticky-cart .single_add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):after, .rmenupro-mobile-sticky-cart .add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):after{
            display: none;
        }

        .rmenupro-mobile-sticky-cart .single_add_to_cart_button.button.alt:hover,
        .rmenupro-mobile-sticky-cart button[name="add-to-cart"]:hover {
            background: #e5e5e5;
        }

        .rmenupro-mobile-sticky-cart .opqcfw-btn,
        .rmenupro-mobile-sticky-cart .onepaquc-checkout-btn {
            grid-column: 2;
        }

        .rmenupro-mobile-sticky-cart .opqcfw-btn:hover,
        .rmenupro-mobile-sticky-cart .onepaquc-checkout-btn:hover {
            background: #333333 !important;
        }

        .rmenupro-mobile-sticky-cart .onepaquc-icon svg,
        .rmenupro-mobile-sticky-cart .rmenupro-icon {
            width: 18px;
            height: 18px;
        }

        .rmenupro-mobile-sticky-cart .button-group.single-button {
            grid-template-columns: 1fr;
        }

        @media (max-width: 380px) {
            .rmenupro-mobile-sticky-cart .cart {
                padding: 12px 16px;
            }

            .rmenupro-mobile-sticky-cart .button-group {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .rmenupro-mobile-sticky-cart button,
            .rmenupro-mobile-sticky-cart .button {
                height: 48px;
                font-size: 14px;
            }
        }

        @media (min-width: 769px) {
            .rmenupro-mobile-sticky-cart {
                display: none !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButton = document.querySelector('.single_add_to_cart_button');

            // Only proceed if we found the add to cart button
            if (!addToCartButton) {
                return;
            }

            // Create the sticky container
            const stickyContainer = document.createElement('div');
            stickyContainer.className = 'rmenupro-mobile-sticky-cart';

            // Clone the add to cart form
            const productForm = document.querySelector('form.cart');
            const clonedForm = productForm.cloneNode(true);

            // Wrap form content in button-group for grid layout
            const formElements = clonedForm.children;
            const buttonGroup = document.createElement('div');
            buttonGroup.className = 'button-group';

            // Find and move buttons to button group
            const buttons = clonedForm.querySelectorAll('button, a.button, .opqcfw-btn, .onepaquc-checkout-btn');
            buttons.forEach(btn => {
                buttonGroup.appendChild(btn);
            });

            // Append button group to form
            clonedForm.appendChild(buttonGroup);

            // Set custom text if provided
            const mobileText = '<?php echo esc_js($mobile_text); ?>';
            if (mobileText) {
                const buttonText = clonedForm.querySelector('.single_add_to_cart_button');
                if (buttonText) {
                    // Check if it's a text node or has innerHTML
                    const iconElement = buttonText.querySelector('.onepaquc-icon');
                    if (iconElement) {
                        // Preserve icon, just update text
                        const textNode = Array.from(buttonText.childNodes).find(node => node.nodeType === 3);
                        if (textNode) {
                            textNode.textContent = ' ' + mobileText;
                        }
                    } else {
                        buttonText.textContent = mobileText;
                    }
                }
            }

            // Add product name section
            const productTitle = document.querySelector('.product_title, h1.entry-title');
            if (productTitle) {
                const productInfo = document.createElement('div');
                productInfo.className = 'product-info';
                productInfo.innerHTML = '<span class="product-name">' + productTitle.textContent + '</span>';
                clonedForm.insertBefore(productInfo, clonedForm.firstChild);
            }

            // Add the cloned form to the sticky container
            stickyContainer.appendChild(clonedForm);

            // Add to page
            document.body.appendChild(stickyContainer);

            // Only show on mobile screens
            const mediaQuery = window.matchMedia('(max-width: 768px)');

            // Handle visibility based on scroll position and screen size
            function handleStickyVisibility() {
                if (mediaQuery.matches) {
                    const scrollPosition = window.scrollY || window.pageYOffset;
                    const originalButtonPosition = addToCartButton.getBoundingClientRect().top + window.scrollY;

                    // Show sticky button when original is out of view
                    if (scrollPosition > originalButtonPosition + 100) {
                        stickyContainer.classList.add('visible');
                    } else {
                        stickyContainer.classList.remove('visible');
                    }
                } else {
                    // On desktop, always hide
                    stickyContainer.classList.remove('visible');
                }
            }

            // Initial check
            handleStickyVisibility();

            // Listen for scroll events with throttle for better performance
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (scrollTimeout) {
                    window.cancelAnimationFrame(scrollTimeout);
                }
                scrollTimeout = window.requestAnimationFrame(handleStickyVisibility);
            });

            // Listen for screen size changes
            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', handleStickyVisibility);
            } else {
                // Fallback for older browsers
                mediaQuery.addListener(handleStickyVisibility);
            }

            // Forward events from sticky button to original form
            const stickyAddToCartBtn = clonedForm.querySelector('.single_add_to_cart_button');
            if (stickyAddToCartBtn) {
                stickyAddToCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get form values from sticky form
                    const stickyQuantity = clonedForm.querySelector('input.qty');
                    const stickyQuantityValue = stickyQuantity ? stickyQuantity.value : 1;

                    // Get variations if they exist
                    const stickyVariations = clonedForm.querySelectorAll('select[name^="attribute_"]');

                    // Update original form quantity
                    const originalQuantity = productForm.querySelector('input.qty');
                    if (originalQuantity) {
                        originalQuantity.value = stickyQuantityValue;
                    }

                    // Update original form variations
                    if (stickyVariations.length > 0) {
                        stickyVariations.forEach(stickySelect => {
                            const attrName = stickySelect.getAttribute('name');
                            const originalSelect = productForm.querySelector('select[name="' + attrName + '"]');
                            if (originalSelect) {
                                originalSelect.value = stickySelect.value;
                            }
                        });
                    }

                    // Trigger click on original button
                    addToCartButton.click();
                });
            }

            // Sync quantity changes between sticky and original forms
            const stickyQtyInput = clonedForm.querySelector('input.qty');
            const originalQtyInput = productForm.querySelector('input.qty');

            if (stickyQtyInput && originalQtyInput) {
                stickyQtyInput.addEventListener('change', function() {
                    originalQtyInput.value = this.value;
                });

                originalQtyInput.addEventListener('change', function() {
                    stickyQtyInput.value = this.value;
                });
            }
        });
    </script>
<?php
}

/**
 * Add jQuery to handle visibility of custom width field
 */
function onepaqucpro_add_to_cart_admin_script()
{
    if (!is_admin()) {
        return;
    }
?>
    <script>
        jQuery(document).ready(function($) {
            // Handle visibility of custom width field
            function toggleCustomWidthField() {
                if ($('select[name="rmenupro_add_to_cart_width"]').val() === 'custom') {
                    $('#rmenupro-atc-custom-width-row').css('display', 'flex');
                } else {
                    $('#rmenupro-atc-custom-width-row').hide();
                }
            }

            // Handle visibility of icon position field
            function toggleIconPositionField() {
                if ($('select[name="rmenupro_add_to_cart_icon"]').val() === 'none') {
                    $('#rmenupro-atc-icon-position-row').hide();
                } else {
                    $('#rmenupro-atc-icon-position-row').show();
                }
            }

            // Handle visibility of custom CSS field
            function toggleCustomCssField() {
                if ($('select[name="rmenupro_add_to_cart_style"]').val() === 'custom') {
                    $('#rmenupro-atc-custom-css-row').show();
                } else {
                    $('#rmenupro-atc-custom-css-row').hide();
                }
            }

            // Initial state
            toggleCustomWidthField();
            toggleIconPositionField();
            toggleCustomCssField();

            // On change events
            $('select[name="rmenupro_add_to_cart_width"]').on('change', toggleCustomWidthField);
            $('select[name="rmenupro_add_to_cart_icon"]').on('change', toggleIconPositionField);
            $('select[name="rmenupro_add_to_cart_style"]').on('change', toggleCustomCssField);
        });
    </script>
    <?php
}




/**
 * Handles the add to cart functionality based on plugin settings
 */

class ONEPAQUCPRO_Add_To_Cart_Handler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize hooks based on settings
        $this->init_hooks();
    }

    /**
     * Initialize hooks based on settings
     */
    public function init_hooks()
    {
        // Setup AJAX add to cart if enabled
        if (get_option('rmenupro_enable_ajax_add_to_cart', 1)) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_ajax_scripts'));
            add_filter('woocommerce_add_to_cart_fragments', array($this, 'add_to_cart_fragments'));
        }

        // Add quantity selector to archive pages if enabled
        if (get_option('rmenupro_show_quantity_archive', 0) && onepaqucpro_premium_feature()) {
            add_action('woocommerce_after_shop_loop_item', array($this, 'add_quantity_field'), 9);
        }

        // Set default quantity
        add_filter('woocommerce_quantity_input_args', array($this, 'set_default_quantity'), 10, 2);

        // Handle redirect after add to cart
        $redirect_option = get_option('rmenupro_redirect_after_add', 'none');
        if ($redirect_option !== 'none') {
            add_filter('woocommerce_add_to_cart_redirect', array($this, 'redirect_after_add_to_cart'));
        }

        // Customize add to cart message
        add_filter('wc_add_to_cart_message_html', array($this, 'customize_add_to_cart_message'), 10, 2);

        // Add animation and notification scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_notification_scripts'));

        // Replace add to cart button if AJAX is enabled
        if (get_option('rmenupro_enable_ajax_add_to_cart', 1)) {
            add_filter('woocommerce_loop_add_to_cart_link', array($this, 'replace_add_to_cart_button'), 10, 2);
        }
    }

    /**
     * Enqueue scripts for AJAX functionality
     */
    public function enqueue_ajax_scripts()
    {
        wp_enqueue_script(
            'rmenupro-ajax-add-to-cart',
            plugin_dir_url(__FILE__) . '../assets/js/add-to-cart.js',
            array('jquery'),
            RMENUPRO_VERSION,
            true
        );

        // Get cart URL
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : WC()->cart->get_cart_url();

        // Get checkout URL
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : WC()->cart->get_checkout_url();

        $redirect_option = get_option('rmenu_redirect_after_add', 'none');
        $redirect_url = 'none';

        if ($redirect_option === 'cart') {
            $redirect_url = $cart_url;
        } elseif ($redirect_option === 'checkout') {
            $redirect_url = $checkout_url;
        }

        wp_localize_script('rmenupro-ajax-add-to-cart', 'rmenupro_ajax_object', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('rmenupro-ajax-nonce')),
            'animation' => get_option('rmenupro_add_to_cart_animation', 'none'),
            'notification_style' => !onepaqucpro_premium_feature() ? "default" : get_option('rmenupro_add_to_cart_notification_style', 'default'),
            'notification_duration' => intval(get_option('rmenupro_add_to_cart_notification_duration', 3000)),
            'redirect' => $redirect_option !== 'none',
            'redirect_url' => $redirect_url,
            'i18n' => array(
                'success' => get_option('rmenupro_add_to_cart_success_message', '{product} has been added to your cart.'),
                'view_cart' => get_option('rmenupro_show_view_cart_link', 1) ? esc_html__('View Cart', 'one-page-quick-checkout-for-woocommerce-pro') : '',
                'checkout' => get_option('rmenupro_show_checkout_link', 0) ? esc_html__('Checkout', 'one-page-quick-checkout-for-woocommerce-pro') : '',
            )
        ));
    }

    /**
     * Add quantity field before add to cart button on archive pages
     */
    public function add_quantity_field()
    {
        global $product;

        if ($product && $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
            if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
                $default_qty = get_option('rmenupro_add_to_cart_default_qty', '1');
            } else {
                $default_qty = 1; // Default to 1 if premium feature is not available
            }

            // Add a unique class to help identify which product this quantity belongs to
            echo '<div class="rmenupro-quantity-wrapper" data-product_id="' . esc_attr($product->get_id()) . '">';

            woocommerce_quantity_input(array(
                'min_value' => 1,
                'max_value' => $product->get_max_purchase_quantity(),
                'input_value' => $default_qty,
                'classes' => 'rmenupro-archive-quantity',
                'input_id' => 'quantity_' . $product->get_id(), // Add a unique ID
            ));

            echo '</div>';
        }
    }

    /**
     * Set default quantity for products
     */
    public function set_default_quantity($args, $product)
    {
        if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
            $default_qty = get_option('rmenupro_add_to_cart_default_qty', '1');
        } else {
            $default_qty = 1; // Default to 1 if premium feature is not available
        }

        if (is_numeric($default_qty) && $default_qty > 0) {
            $args['input_value'] = $default_qty;
        }

        return $args;
    }

    /**
     * Handle redirect after add to cart
     */
    public function redirect_after_add_to_cart($url)
    {
        $redirect_option = get_option('rmenupro_redirect_after_add', 'none');

        if ($redirect_option === 'cart') {
            return wc_get_cart_url();
        } elseif ($redirect_option === 'checkout') {
            return wc_get_checkout_url();
        }

        return $url;
    }

    /**
     * Customize the add to cart message
     */
    public function customize_add_to_cart_message($message, $products)
    {
        $custom_message = get_option('rmenupro_add_to_cart_success_message', '{product} has been added to your cart.');
        $show_view_cart = get_option('rmenupro_show_view_cart_link', 1);
        $show_checkout = get_option('rmenupro_show_checkout_link', 0);

        $titles = array();
        $count = 0;

        foreach ($products as $product_id => $qty) {
            $titles[] = get_the_title($product_id);
            $count += $qty;
        }

        $product_name = join(', ', $titles);
        $custom_message = str_replace('{product}', $product_name, $custom_message);

        $message = '<div>' . esc_html($custom_message);

        if ($show_view_cart) {
            $message .= ' <a href="' . esc_url(wc_get_cart_url()) . '" class="button wc-forward">' . esc_html__('View cart', 'one-page-quick-checkout-for-woocommerce-pro') . '</a>';
        }

        if ($show_checkout) {
            $message .= ' <a href="' . esc_url(wc_get_checkout_url()) . '" class="button checkout wc-forward">' . esc_html__('Checkout', 'one-page-quick-checkout-for-woocommerce-pro') . '</a>';
        }

        $message .= '</div>';

        return $message;
    }

    /**
     * Enqueue scripts for notifications and animations
     */
    public function enqueue_notification_scripts()
    {
        wp_enqueue_style(
            'rmenupro-notifications',
            plugin_dir_url(__FILE__) . '../assets/css/notifications.css',
            array(),
            RMENUPRO_VERSION
        );
    }

    /**
     * Replace add to cart button with AJAX version
     */
    public function replace_add_to_cart_button($html, $product)
    {
        if ($product && $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
            // Get default quantity from settings
            if (function_exists('onepaqucpro_premium_feature') && onepaqucpro_premium_feature()) {
                $default_qty = get_option('rmenupro_add_to_cart_default_qty', '1');
            } else {
                $default_qty = 1; // Default to 1 if premium feature is not available
            }

            $html = sprintf(
                '<a href="%s" data-quantity="%s" class="%s" %s data-product_id="%d" data-product_sku="%s" data-default_qty="%s" aria-label="%s" rel="nofollow">%s</a>',
                esc_url($product->add_to_cart_url()),
                esc_attr($default_qty), // Set default quantity here
                esc_attr('button product_type_simple add_to_cart_button onepaqucpro_ajax_add_to_cart rmenupro-ajax-add-to-cart'),
                '',
                esc_attr($product->get_id()),
                esc_attr($product->get_sku()),
                esc_attr($default_qty), // Add default quantity as data attribute
                esc_html($product->add_to_cart_description()),
                esc_html($product->add_to_cart_text())
            );
        }

        return $html;
    }

    /**
     * Add cart fragments for AJAX cart updates
     */
    public function add_to_cart_fragments($fragments)
    {
        // Update mini cart content
        ob_start();
    ?>
        <span class="rmenupro-cart-count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            ?></span>
        <?php
        $fragments['span.rmenupro-cart-count'] = ob_get_clean();

        // Update cart total
        ob_start();
        ?>
        <span class="rmenu-cart-total"><?php echo esc_html(WC()->cart->get_cart_total()); ?></span>
<?php
        $fragments['span.rmenupro-cart-total'] = ob_get_clean();

        return $fragments;
    }
}
