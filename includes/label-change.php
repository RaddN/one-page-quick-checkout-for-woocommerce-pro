<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Customize WooCommerce checkout text labels
function onepaqucpro_custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    global $onepaqucpro_checkoutformfields, $onepaqucpro_productpageformfields;
    // convert $onepaqucpro_checkoutformfields to array $mapping
    $mapping = array_merge(array_flip($onepaqucpro_checkoutformfields), array_flip($onepaqucpro_productpageformfields));


    if ($domain === 'woocommerce' && array_key_exists($text, $mapping)) {
        $option_key = $mapping[$text];
        $translated_text = get_option($option_key) ? esc_attr(get_option($option_key)) : $onepaqucpro_checkoutformfields[$option_key] ?? $onepaqucpro_productpageformfields[$option_key];
    }

    return $translated_text;
}
add_filter('gettext', 'onepaqucpro_custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function onepaqucpro_custom_woocommerce_shipping_label($label, $package_name)
{
    return get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'onepaqucpro_custom_woocommerce_shipping_label', 10, 2);

