<?php

/**
 * WooCommerce Checkout Form Customizer
 * Modifies checkout form based on JSON configuration
 */

class WooCommerce_Checkout_Customizer
{

    private $config;

    public function __construct()
    {
        add_action('init', array($this, 'init_customizer'));
    }

    public function init_customizer()
    {
        // Get the configuration
        $checkout_form_setup = get_option("checkout_form_setup", '');
        $this->config = json_decode($checkout_form_setup, true);

        if (!$this->config) {
            return;
        }

        // Hook into WooCommerce checkout
        add_filter('woocommerce_checkout_fields', array($this, 'customize_checkout_fields'), 20);
        add_filter('woocommerce_checkout_show_terms', array($this, 'customize_terms_display'), 20);
        add_filter('woocommerce_order_button_text', array($this, 'customize_order_button_text'), 20);
        add_action('wp_loaded', array($this, 'customize_ship_to_different'), 20);

        // Replace existing text
        add_filter('gettext', array($this, 'replace_woocommerce_text'), 20, 3);
        add_filter('ngettext', array($this, 'replace_woocommerce_text'), 20, 5);

        // Handle visibility with CSS
        add_action('wp_head', array($this, 'add_visibility_styles'));

        add_filter('woocommerce_shipping_package_name', array($this, 'onepaquc_custom_woocommerce_shipping_label'), 10, 2);
    }

    /**
     * Customize checkout fields based on configuration
     */
    public function customize_checkout_fields($fields)
    {

        // Billing fields mapping
        $billing_mapping = array(
            'first-name' => 'billing_first_name',
            'last-name' => 'billing_last_name',
            'email' => 'billing_email',
            'phone' => 'billing_phone',
            'country' => 'billing_country',
            'address' => 'billing_address_1',
            'address2' => 'billing_address_2',
            'city' => 'billing_city',
            'state' => 'billing_state',
            'postcode' => 'billing_postcode',
            'company' => 'billing_company'
        );

        // Shipping fields mapping
        $shipping_mapping = array(
            'shipping-first-name' => 'shipping_first_name',
            'shipping-last-name' => 'shipping_last_name',
            'shipping-country' => 'shipping_country',
            'shipping-address' => 'shipping_address_1',
            'shipping-address2' => 'shipping_address_2',
            'shipping-city' => 'shipping_city',
            'shipping-state' => 'shipping_state',
            'shipping-postcode' => 'shipping_postcode',
            'shipping-company' => 'shipping_company'
        );

        // Customize billing fields
        foreach ($billing_mapping as $config_key => $field_key) {
            if (isset($this->config[$config_key])) {
                $config = $this->config[$config_key];

                if (!$config['visible']) {
                    // Hide field if not visible
                    unset($fields['billing'][$field_key]);
                } else {
                    // Update field properties
                    if (isset($fields['billing'][$field_key])) {
                        if (isset($config['label'])) {
                            $fields['billing'][$field_key]['label'] = $config['label'];
                        }
                        if (isset($config['required'])) {
                            $fields['billing'][$field_key]['required'] = $config['required'];
                        }
                        if (isset($config['placeholder'])) {
                            $fields['billing'][$field_key]['placeholder'] = $config['placeholder'];
                        }
                    }
                }
            }
        }

        // Customize shipping fields
        foreach ($shipping_mapping as $config_key => $field_key) {
            if (isset($this->config[$config_key])) {
                $config = $this->config[$config_key];

                if (!$config['visible']) {
                    unset($fields['shipping'][$field_key]);
                } else {
                    if (isset($fields['shipping'][$field_key])) {
                        if (isset($config['label'])) {
                            $fields['shipping'][$field_key]['label'] = $config['label'];
                        }
                        if (isset($config['required'])) {
                            $fields['shipping'][$field_key]['required'] = $config['required'];
                        }
                        if (isset($config['placeholder'])) {
                            $fields['shipping'][$field_key]['placeholder'] = $config['placeholder'];
                        }
                    }
                }
            }
        }

        // Handle order notes
        if (isset($this->config['order-notes'])) {
            $notes_config = $this->config['order-notes'];
            if (!$notes_config['visible']) {
                unset($fields['order']['order_comments']);
            } else {
                if (isset($fields['order']['order_comments'])) {
                    if (isset($notes_config['label'])) {
                        $fields['order']['order_comments']['label'] = $notes_config['label'];
                    }
                    if (isset($notes_config['placeholder'])) {
                        $fields['order']['order_comments']['placeholder'] = $notes_config['placeholder'];
                    }
                    if (isset($notes_config['required'])) {
                        $fields['order']['order_comments']['required'] = $notes_config['required'];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Replace WooCommerce default text with custom text from configuration
     */
    public function replace_woocommerce_text($translated_text, $text, $domain)
    {
        // Only modify WooCommerce text
        if ($domain !== 'woocommerce') {
            return $translated_text;
        }

        // Replace coupon section text
        if (isset($this->config['coupon-section']) && $this->config['coupon-section']['visible']) {
            $coupon_config = $this->config['coupon-section'];

            if ($text === 'Have a coupon?' && isset($coupon_config['title'])) {
                return $coupon_config['title'];
            }

            if ($text === 'Apply coupon' && isset($coupon_config['button'])) {
                return $coupon_config['button'];
            }

            if ($text === 'Click here to enter your code' && isset($coupon_config['description'])) {
                return $coupon_config['description'];
            }
            if ($text === 'Coupon code' && isset($coupon_config['placeholder'])) {
                // You can customize the coupon input placeholder via this
                return $coupon_config['placeholder'];
            }
        }

        // Replace billing details title
        if (isset($this->config['billing-title']) && $this->config['billing-title']['visible']) {
            if ($text === 'Billing details' && isset($this->config['billing-title']['text'])) {
                return $this->config['billing-title']['text'];
            }
        }

        // Replace shipping title
        if (isset($this->config['shipping-title']) && $this->config['shipping-title']['visible']) {
            if ($text === 'Ship to a different address?' && isset($this->config['ship-to-different']['label'])) {
                return $this->config['ship-to-different']['label'];
            }
        }

        // Replace additional information title
        if (isset($this->config['additional-title']) && $this->config['additional-title']['visible']) {
            if ($text === 'Additional information' && isset($this->config['additional-title']['text'])) {
                return $this->config['additional-title']['text'];
            }
        }

        // Replace order review title
        if (isset($this->config['order-review-title']) && $this->config['order-review-title']['visible']) {
            if ($text === 'Your order' && isset($this->config['order-review-title']['text'])) {
                return $this->config['order-review-title']['text'];
            }
        }
        if (isset($this->config['product-header']) && $this->config['product-header']['visible']) {
            if ($text === 'Product' && isset($this->config['product-header']['text'])) {
                return $this->config['product-header']['text'];
            }
        }
        if (isset($this->config['subtotal-header'])) {
            if ($text === 'Subtotal' && isset($this->config['subtotal-header']['text'])) {
                return $this->config['subtotal-header']['text'];
            }
        }
        if (isset($this->config['total-header']) && $this->config['total-header']['visible']) {
            if ($text === 'Total' && isset($this->config['total-header']['text'])) {
                return $this->config['total-header']['text'];
            }
        }
        if (isset($this->config['shipping']) && $this->config['shipping']['visible']) {
            if ($text === 'Shipping' && isset($this->config['shipping']['text'])) {
                return $this->config['shipping']['text'];
            }
        }

        return $translated_text;
    }



    /**
     * Customize order button text
     */
    public function customize_order_button_text($button_text)
    {
        if (isset($this->config['place-order'])) {
            return $this->config['place-order']['text'];
        }
        return $button_text;
    }

    /**
     * Customize terms and privacy policy display
     */
    public function customize_terms_display($show_terms)
    {
        // Replace privacy policy text instead of adding new one
        add_filter('woocommerce_get_privacy_policy_text', array($this, 'replace_privacy_policy_text'));
        return $show_terms;
    }

    /**
     * Replace existing privacy policy text
     */
    public function replace_privacy_policy_text($privacy_text)
    {
        if (isset($this->config['privacy-policy']) && $this->config['privacy-policy']['visible']) {
            return $this->config['privacy-policy']['text'];
        }
        return $privacy_text;
    }

    /**
     * Handle ship to different address option
     */
    public function customize_ship_to_different()
    {
        if (isset($this->config['ship-to-different']) && !$this->config['ship-to-different']['visible']) {
            add_filter('woocommerce_cart_needs_shipping_address', '__return_false');
        }
    }
    /**
     * Handle section visibility with CSS
     */
    public function add_visibility_styles()
    {
        if (!is_checkout()) {
            return;
        }

        echo '<style type="text/css">';

        // Hide entire billing section
        if (isset($this->config['billing-title']) && !$this->config['billing-title']['visible']) {
            echo '.woocommerce-billing-fields h3 { display: none !important; }';
        }

        // Hide entire shipping section
        if (isset($this->config['shipping-title']) && !$this->config['shipping-title']['visible']) {
            echo '.woocommerce-shipping-fields h3 { display: none !important; }';
        }

        // Hide additional information section
        if (isset($this->config['additional-title']) && !$this->config['additional-title']['visible']) {
            echo '.woocommerce-additional-fields h3 { display: none !important; }';
        }

        // Hide order review section
        if (isset($this->config['order-review-title']) && !$this->config['order-review-title']['visible']) {
            echo '#order_review_heading { display: none !important; }';
        }

        // Hide product header
        if (isset($this->config['product-header']) && !$this->config['product-header']['visible']) {
            echo '.shop_table thead th:first-child { display: none !important; }';
        }

        // Hide subtotal header
        if (isset($this->config['subtotal-header']) && !$this->config['subtotal-header']['visible']) {
            echo '.shop_table thead th:last-child { display: none !important; }';
        }
        if (isset($this->config['order-item']) && !$this->config['order-item']['visible']) {
            echo '.shop_table tr.cart_item td.product-name{ display: none !important; }';
        }
        if (isset($this->config['order-item-price']) && !$this->config['order-item-price']['visible']) {
            echo '.shop_table tr.cart_item td.product-total{ display: none !important; }';
        }
        if (isset($this->config['subtotal2']) && !$this->config['subtotal2']['visible']) {
            echo '.shop_table tfoot tr.cart-subtotal th{ display: none !important; }';
        }
        if (isset($this->config['subtotal-price']) && !$this->config['subtotal-price']['visible']) {
            echo '.shop_table tfoot tr.cart-subtotal td{ display: none !important; }';
        }
        if (isset($this->config['shipping']) && !$this->config['shipping']['visible']) {
            echo '.shop_table tfoot tr.shipping th{ display: none !important; }';
        }
        if (isset($this->config['shipping-price']) && !$this->config['shipping-price']['visible']) {
            echo '.shop_table tfoot tr.shipping td{ display: none !important; }';
        }
        if (isset($this->config['total-header']) && !$this->config['total-header']['visible']) {
            echo '.shop_table tfoot tr:last-child th{ display: none !important; }';
        }
        if (isset($this->config['total-price']) && !$this->config['total-price']['visible']) {
            echo '.shop_table tfoot tr:last-child td{ display: none !important; }';
        }
        if (!$this->config['order-summary']['visible']) {
            echo '.shop_table{ display: none !important; }';
        }

        // Hide payment methods section
        if (isset($this->config['payment-methods']) && !$this->config['payment-methods']['visible']) {
            echo '#payment ul.wc_payment_methods.payment_methods.methods { display: none !important; }';
        }

        // Hide coupon section
        if (isset($this->config['coupon-section']) && !$this->config['coupon-section']['visible']) {
            echo '.woocommerce-form-coupon-toggle, .checkout_coupon { display: none !important; }';
        }

        // Hide place order button
        if (isset($this->config['place-order']) && !$this->config['place-order']['visible']) {
            echo '#place_order { display: none !important; }';
        }

        // Hide privacy policy
        if (isset($this->config['privacy-policy']) && !$this->config['privacy-policy']['visible']) {
            echo '.woocommerce-terms-and-conditions-wrapper { display: none !important; }';
        }

        // Hide terms and conditions
        if (isset($this->config['terms-and-conditions']) && !$this->config['terms-and-conditions']['visible']) {
            echo '.woocommerce-terms-and-conditions-wrapper { display: none !important; }';
        }

        // Hide individual payment methods
        if (isset($this->config['payment-methods'])) {
            foreach ($this->config['payment-methods'] as $gateway_id => $gateway_config) {
                if (isset($gateway_config['visible']) && !$gateway_config['visible']) {
                    echo '.payment_method_' . esc_attr($gateway_id) . ' { display: none !important; }';
                }
            }
        }

        echo '</style>';
    }

    // Change "Shipping" label in WooCommerce shipping totals section
   public function onepaquc_custom_woocommerce_shipping_label($label, $package_name)
    {
        if (isset($this->config['shipping']) && $this->config['shipping']['visible'] && isset($this->config['shipping']['text'])) {
                return $this->config['shipping']['text'];
        }
    }
}

// Initialize the customizer
new WooCommerce_Checkout_Customizer();
