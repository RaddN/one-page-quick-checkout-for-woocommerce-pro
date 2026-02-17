<?php

/**
 * WooCommerce Checkout Form Customizer
 * Modifies checkout form based on JSON configuration
 */

class WooCommerce_Checkout_Customizer
{

    private $config;

    /**
     * Normalize mixed values into booleans.
     */
    private function normalize_bool($value, $default = true)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, array('1', 'true', 'yes', 'on'), true)) {
                return true;
            }
            if (in_array($value, array('0', 'false', 'no', 'off', ''), true)) {
                return false;
            }
        }

        if (is_null($value)) {
            return $default;
        }

        return (bool) $value;
    }

    /**
     * Read "visible" from a config node safely.
     */
    private function is_config_visible($config, $default = true)
    {
        if (!is_array($config) || !array_key_exists('visible', $config)) {
            return $default;
        }
        return $this->normalize_bool($config['visible'], $default);
    }

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
        add_filter('woocommerce_billing_fields', array($this, 'customize_billing_fields'), 20, 2);
        add_filter('woocommerce_shipping_fields', array($this, 'customize_shipping_fields'), 20, 2);
        add_filter('woocommerce_default_address_fields', array($this, 'customize_default_address_fields'), 20);
        add_filter('woocommerce_get_country_locale_default', array($this, 'customize_country_locale_default'), 20);
        add_filter('woocommerce_get_country_locale', array($this, 'customize_country_locale'), 20);
        add_filter('woocommerce_checkout_show_terms', array($this, 'customize_terms_display'), 20);
        add_filter('woocommerce_enable_order_notes_field', array($this, 'customize_order_notes_enabled'), 20);
        add_filter('woocommerce_order_button_text', array($this, 'customize_order_button_text'), 20);
        add_action('wp_loaded', array($this, 'customize_ship_to_different'), 20);

        // Replace existing text
        add_filter('gettext', array($this, 'replace_woocommerce_text'), 20, 3);
        add_filter('ngettext', array($this, 'replace_woocommerce_text'), 20, 5);

        // Handle visibility with CSS
        add_action('wp_head', array($this, 'add_visibility_styles'));

        add_filter('woocommerce_shipping_package_name', array($this, 'onepaqucpro_custom_woocommerce_shipping_label'), 10, 2);
    }

    /**
     * Return billing field mapping (config key => WooCommerce field key).
     */
    private function get_billing_mapping()
    {
        return array(
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
            'company' => 'billing_company',
        );
    }

    /**
     * Return shipping field mapping (config key => WooCommerce field key).
     */
    private function get_shipping_mapping()
    {
        return array(
            'shipping-first-name' => 'shipping_first_name',
            'shipping-last-name' => 'shipping_last_name',
            'shipping-country' => 'shipping_country',
            'shipping-address' => 'shipping_address_1',
            'shipping-address2' => 'shipping_address_2',
            'shipping-city' => 'shipping_city',
            'shipping-state' => 'shipping_state',
            'shipping-postcode' => 'shipping_postcode',
            'shipping-company' => 'shipping_company',
        );
    }

    /**
     * Return default address mapping used for country locale + block checkout.
     */
    private function get_default_address_mapping()
    {
        return array(
            'first-name' => 'first_name',
            'last-name' => 'last_name',
            'country' => 'country',
            'address' => 'address_1',
            'address2' => 'address_2',
            'city' => 'city',
            'state' => 'state',
            'postcode' => 'postcode',
            'company' => 'company',
        );
    }

    /**
     * Apply a single field config into a field definition array.
     */
    private function apply_single_field_config($field, $config, $allow_hidden_property = false)
    {
        if (isset($config['label'])) {
            $field['label'] = $config['label'];
        }

        if (isset($config['required'])) {
            $field['required'] = (bool) $config['required'];
        }

        if (isset($config['placeholder'])) {
            $field['placeholder'] = $config['placeholder'];
        }

        if ($allow_hidden_property && array_key_exists('visible', $config)) {
            $is_visible = $this->is_config_visible($config, true);
            $field['hidden'] = !$is_visible;
            if (!$is_visible) {
                $field['required'] = false;
            }
        }

        return $field;
    }

    /**
     * Apply config mapping to a field group.
     */
    private function apply_group_config($fields, $mapping, $remove_when_hidden = true, $allow_hidden_property = false)
    {
        foreach ($mapping as $config_key => $field_key) {
            if (!isset($this->config[$config_key])) {
                continue;
            }

            $config = $this->config[$config_key];
            $is_hidden = !$this->is_config_visible($config, true);

            if ($remove_when_hidden && $is_hidden) {
                unset($fields[$field_key]);
                continue;
            }

            if (!isset($fields[$field_key])) {
                continue;
            }

            $fields[$field_key] = $this->apply_single_field_config(
                $fields[$field_key],
                $config,
                $allow_hidden_property
            );
        }

        return $fields;
    }

    /**
     * Customize checkout fields based on configuration
     */
    public function customize_checkout_fields($fields)
    {
        if (isset($fields['billing']) && is_array($fields['billing'])) {
            $fields['billing'] = $this->apply_group_config(
                $fields['billing'],
                $this->get_billing_mapping(),
                true,
                false
            );
        }

        if (isset($fields['shipping']) && is_array($fields['shipping'])) {
            $fields['shipping'] = $this->apply_group_config(
                $fields['shipping'],
                $this->get_shipping_mapping(),
                true,
                false
            );
        }

        // Handle order notes
        if (isset($this->config['order-notes'])) {
            $notes_config = $this->config['order-notes'];
            if (!$this->is_config_visible($notes_config, true)) {
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
     * Customize billing fields in contexts that use woocommerce_billing_fields.
     */
    public function customize_billing_fields($fields)
    {
        if (!is_array($fields)) {
            return $fields;
        }

        return $this->apply_group_config(
            $fields,
            $this->get_billing_mapping(),
            true,
            false
        );
    }

    /**
     * Customize shipping fields in contexts that use woocommerce_shipping_fields.
     */
    public function customize_shipping_fields($fields)
    {
        if (!is_array($fields)) {
            return $fields;
        }

        return $this->apply_group_config(
            $fields,
            $this->get_shipping_mapping(),
            true,
            false
        );
    }

    /**
     * Customize default address fields so country locale + Blocks receive the changes.
     */
    public function customize_default_address_fields($fields)
    {
        if (!is_array($fields)) {
            return $fields;
        }

        return $this->apply_group_config(
            $fields,
            $this->get_default_address_mapping(),
            false,
            true
        );
    }

    /**
     * Build locale overrides from config for address fields.
     */
    private function get_locale_overrides()
    {
        $locale_overrides = array();
        $mapping = $this->get_default_address_mapping();

        foreach ($mapping as $config_key => $field_key) {
            if (!isset($this->config[$config_key])) {
                continue;
            }

            $config = $this->config[$config_key];
            $override = array();

            if (isset($config['label'])) {
                $override['label'] = $config['label'];
            }

            if (isset($config['placeholder'])) {
                $override['placeholder'] = $config['placeholder'];
            }

            if (isset($config['required'])) {
                $override['required'] = (bool) $config['required'];
            }

            if (array_key_exists('visible', $config)) {
                $is_visible = $this->is_config_visible($config, true);
                $override['hidden'] = !$is_visible;
                if (!$is_visible) {
                    $override['required'] = false;
                }
            }

            if (!empty($override)) {
                $locale_overrides[$field_key] = $override;
            }
        }

        return $locale_overrides;
    }

    /**
     * Apply config to default country locale values.
     */
    public function customize_country_locale_default($locale)
    {
        if (!is_array($locale)) {
            return $locale;
        }

        $overrides = $this->get_locale_overrides();

        foreach ($overrides as $field_key => $field_override) {
            if (!isset($locale[$field_key]) || !is_array($locale[$field_key])) {
                $locale[$field_key] = array();
            }
            $locale[$field_key] = array_merge($locale[$field_key], $field_override);
        }

        return $locale;
    }

    /**
     * Apply config to each country locale, preventing per-country overrides from resetting labels.
     */
    public function customize_country_locale($locales)
    {
        if (!is_array($locales)) {
            return $locales;
        }

        $overrides = $this->get_locale_overrides();
        if (empty($overrides)) {
            return $locales;
        }

        foreach ($locales as $country_code => $country_locale) {
            if (!is_array($country_locale)) {
                continue;
            }

            foreach ($overrides as $field_key => $field_override) {
                if (!isset($locales[$country_code][$field_key]) || !is_array($locales[$country_code][$field_key])) {
                    $locales[$country_code][$field_key] = array();
                }
                $locales[$country_code][$field_key] = array_merge($locales[$country_code][$field_key], $field_override);
            }
        }

        return $locales;
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
        if (isset($this->config['coupon-section']) && $this->is_config_visible($this->config['coupon-section'], true)) {
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
        if (isset($this->config['billing-title']) && $this->is_config_visible($this->config['billing-title'], true)) {
            if ($text === 'Billing details' && isset($this->config['billing-title']['text'])) {
                return $this->config['billing-title']['text'];
            }
        }

        // Replace shipping title
        if (isset($this->config['shipping-title']) && $this->is_config_visible($this->config['shipping-title'], true)) {
            if ($text === 'Ship to a different address?' && isset($this->config['ship-to-different']['label'])) {
                return $this->config['ship-to-different']['label'];
            }
        }

        // Replace additional information title
        if (isset($this->config['additional-title']) && $this->is_config_visible($this->config['additional-title'], true)) {
            if ($text === 'Additional information' && isset($this->config['additional-title']['text'])) {
                return $this->config['additional-title']['text'];
            }
        }

        // Replace order review title
        if (isset($this->config['order-review-title']) && $this->is_config_visible($this->config['order-review-title'], true)) {
            if ($text === 'Your order' && isset($this->config['order-review-title']['text'])) {
                return $this->config['order-review-title']['text'];
            }
        }
        if (isset($this->config['product-header']) && $this->is_config_visible($this->config['product-header'], true)) {
            if ($text === 'Product' && isset($this->config['product-header']['text'])) {
                return $this->config['product-header']['text'];
            }
        }
        if (isset($this->config['subtotal-header'])) {
            if ($text === 'Subtotal' && isset($this->config['subtotal-header']['text'])) {
                return $this->config['subtotal-header']['text'];
            }
        }
        if (isset($this->config['total-header']) && $this->is_config_visible($this->config['total-header'], true)) {
            if ($text === 'Total' && isset($this->config['total-header']['text'])) {
                return $this->config['total-header']['text'];
            }
        }
        if (isset($this->config['shipping']) && $this->is_config_visible($this->config['shipping'], true)) {
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
     * Enable/disable order notes field.
     */
    public function customize_order_notes_enabled($enabled)
    {
        if (isset($this->config['order-notes']) && !$this->is_config_visible($this->config['order-notes'], true)) {
            return false;
        }
        return $enabled;
    }

    /**
     * Replace existing privacy policy text
     */
    public function replace_privacy_policy_text($privacy_text)
    {
        if (isset($this->config['privacy-policy']) && $this->is_config_visible($this->config['privacy-policy'], true)) {
            return $this->config['privacy-policy']['text'];
        }
        return $privacy_text;
    }

    /**
     * Handle ship to different address option
     */
    public function customize_ship_to_different()
    {
        if (isset($this->config['ship-to-different']) && !$this->is_config_visible($this->config['ship-to-different'], true)) {
            add_filter('woocommerce_cart_needs_shipping_address', '__return_false');
        }
    }
    /**
     * Handle section visibility with CSS
     */
    public function add_visibility_styles()
    {
        if (
            !is_checkout() &&
            !(function_exists('onepaqucpro_is_checkout_rendered') && onepaqucpro_is_checkout_rendered())
        ) {
            return;
        }

        echo '<style type="text/css">';

        // Hide entire billing section
        if (isset($this->config['billing-title']) && !$this->is_config_visible($this->config['billing-title'], true)) {
            echo '.woocommerce-billing-fields h3, .wc-block-checkout__billing-fields .wc-block-components-checkout-step__heading, .wc-block-checkout__billing-fields .wc-block-components-checkout-step__description, .wp-block-woocommerce-checkout-billing-address-block .wc-block-components-checkout-step__heading, .wp-block-woocommerce-checkout-billing-address-block .wc-block-components-checkout-step__description { display: none !important; }';
        }

        // Hide contact information heading/description when email is hidden.
        if (isset($this->config['email']) && !$this->is_config_visible($this->config['email'], true)) {
            echo '.wc-block-checkout__contact-fields .wc-block-components-checkout-step__heading, .wc-block-checkout__contact-fields .wc-block-components-checkout-step__description, .wp-block-woocommerce-checkout-contact-information-block .wc-block-components-checkout-step__heading, .wp-block-woocommerce-checkout-contact-information-block .wc-block-components-checkout-step__description { display: none !important; }';
        }

        // Hide entire shipping section
        if (isset($this->config['shipping-title']) && !$this->is_config_visible($this->config['shipping-title'], true)) {
            echo '.woocommerce-shipping-fields h3 { display: none !important; }';
        }

        // Hide additional information section
        if (isset($this->config['additional-title']) && !$this->is_config_visible($this->config['additional-title'], true)) {
            echo '.woocommerce-additional-fields h3 { display: none !important; }';
        }

        // Hide order review section
        if (isset($this->config['order-review-title']) && !$this->is_config_visible($this->config['order-review-title'], true)) {
            echo '#order_review_heading { display: none !important; }';
        }

        // Hide product header
        if (isset($this->config['product-header']) && !$this->is_config_visible($this->config['product-header'], true)) {
            echo '.shop_table thead th:first-child { display: none !important; }';
        }

        // Hide subtotal header
        if (isset($this->config['subtotal-header']) && !$this->is_config_visible($this->config['subtotal-header'], true)) {
            echo '.shop_table thead th:last-child { display: none !important; }';
        }
        if (isset($this->config['order-item']) && !$this->is_config_visible($this->config['order-item'], true)) {
            echo '.shop_table tr.cart_item td.product-name{ display: none !important; }';
        }
        if (isset($this->config['order-item-price']) && !$this->is_config_visible($this->config['order-item-price'], true)) {
            echo '.shop_table tr.cart_item td.product-total{ display: none !important; }';
        }
        if (isset($this->config['subtotal2']) && !$this->is_config_visible($this->config['subtotal2'], true)) {
            echo '.shop_table tfoot tr.cart-subtotal th{ display: none !important; }';
        }
        if (isset($this->config['subtotal-price']) && !$this->is_config_visible($this->config['subtotal-price'], true)) {
            echo '.shop_table tfoot tr.cart-subtotal td{ display: none !important; }';
        }
        if (isset($this->config['shipping']) && !$this->is_config_visible($this->config['shipping'], true)) {
            echo '.shop_table tfoot tr.shipping th{ display: none !important; }';
        }
        if (isset($this->config['shipping-price']) && !$this->is_config_visible($this->config['shipping-price'], true)) {
            echo '.shop_table tfoot tr.shipping td{ display: none !important; }';
        }
        if (isset($this->config['total-header']) && !$this->is_config_visible($this->config['total-header'], true)) {
            echo '.shop_table tfoot tr:last-child th{ display: none !important; }';
        }
        if (isset($this->config['total-price']) && !$this->is_config_visible($this->config['total-price'], true)) {
            echo '.shop_table tfoot tr:last-child td{ display: none !important; }';
        }
        if (isset($this->config['order-summary']) && !$this->is_config_visible($this->config['order-summary'], true)) {
            echo '.shop_table, #order_review, .e-checkout__order_review, .e-checkout__order_review-2, .wc-block-components-order-summary, .wc-block-checkout__sidebar, .wp-block-woocommerce-checkout-order-summary-block, .wp-block-woocommerce-checkout-totals-block, .wc-block-checkout__totals, .wc-block-components-checkout-order-summary { display: none !important; }';
        }

        // Hide payment methods section
        if (isset($this->config['payment-methods']) && !$this->is_config_visible($this->config['payment-methods'], true)) {
            echo '#payment, #payment ul.wc_payment_methods.payment_methods.methods, .wc_payment_methods, .woocommerce-checkout-payment, .e-checkout__order_review-2, .wc-block-components-checkout-step--payment, .wc-block-components-checkout-step--payment-methods, .wc-block-checkout__payment-method, .wc-block-checkout__payment-methods, .wc-block-components-payment-methods, .wc-block-components-checkout-payment-methods, .wp-block-woocommerce-checkout-payment-block { display: none !important; }';
        }

        // Hide coupon section
        if (isset($this->config['coupon-section']) && !$this->is_config_visible($this->config['coupon-section'], true)) {
            echo '.woocommerce-form-coupon-toggle, .checkout_coupon, .wc-block-components-totals-coupon, .wc-block-components-totals-coupon-link, .wc-block-components-checkout-step--coupon, .e-coupon-box, .e-woocommerce-coupon-nudge, .e-coupon-anchor, .wp-block-woocommerce-checkout-order-summary-coupon-form-block { display: none !important; }';
        }

        // Hide order notes
        if (isset($this->config['order-notes']) && !$this->is_config_visible($this->config['order-notes'], true)) {
            echo '#order_comments_field, .woocommerce-additional-fields__field-wrapper #order_comments, .wc-block-checkout__order-notes, .wp-block-woocommerce-checkout-order-note-block { display: none !important; }';
        }

        // Hide place order button
        if (isset($this->config['place-order']) && !$this->is_config_visible($this->config['place-order'], true)) {
            echo '#place_order { display: none !important; }';
        }

        // Hide privacy policy
        if (isset($this->config['privacy-policy']) && !$this->is_config_visible($this->config['privacy-policy'], true)) {
            echo '.woocommerce-terms-and-conditions-wrapper { display: none !important; }';
        }

        // Hide terms and conditions
        if (isset($this->config['terms-and-conditions']) && !$this->is_config_visible($this->config['terms-and-conditions'], true)) {
            echo '.woocommerce-terms-and-conditions-wrapper { display: none !important; }';
        }

        // Hide individual payment methods
        if (isset($this->config['payment-methods'])) {
            foreach ($this->config['payment-methods'] as $gateway_id => $gateway_config) {
                if (is_array($gateway_config) && array_key_exists('visible', $gateway_config) && !$this->normalize_bool($gateway_config['visible'], true)) {
                    echo '.payment_method_' . esc_attr($gateway_id) . ' { display: none !important; }';
                }
            }
        }

        echo '</style>';
    }

    // Change "Shipping" label in WooCommerce shipping totals section
   public function onepaqucpro_custom_woocommerce_shipping_label($label, $package_name)
    {
        if (isset($this->config['shipping']) && $this->is_config_visible($this->config['shipping'], true) && isset($this->config['shipping']['text'])) {
                return $this->config['shipping']['text'];
        }
        return $label;
    }
}

// Initialize the customizer
new WooCommerce_Checkout_Customizer();
