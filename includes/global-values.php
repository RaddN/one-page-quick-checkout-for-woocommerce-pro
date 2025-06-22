<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

$onepaqucpro_checkoutformfields = [
    "your_cart" => "Your Cart",
    "txt_subtotal" => "Subtotal",
    "txt_checkout" => "Place order",
    "txt-complete_your_purchase" => "Complete your purchase using the form below.",
];

// archive & single product page text

$onepaqucpro_productpageformfields = [
    "txt-add-to-cart" => "Add to cart",
    "txt-select-options" => "Select options",
    "txt-read-more" => "Read more",
    "rmenupro_grouped_add_to_cart_text" => "View products",
    "rmenupro_out_of_stock_text" => "Out of stock"
];

$onepaqucpro_rcheckoutformfields = [
    'first_name' => ['title' => 'First Name', 'selector' => '#billing_first_name_field, #shipping_first_name_field'],
    'last_name'  => ['title' => 'Last Name', 'selector' => '#billing_last_name_field, #shipping_last_name_field'],
    'country'      => ['title' => 'Country', 'selector' => '#billing_country_field, #shipping_country_field'],
    'state'      => ['title' => 'State / District', 'selector' => '#billing_state_field, #shipping_state_field'],
    'city'       => ['title' => 'City', 'selector' => '#billing_city_field, #shipping_city_field'],
    'postcode'   => ['title' => 'Zip Code', 'selector' => '#billing_postcode_field, #shipping_postcode_field'],
    'address_1'  => ['title' => 'Address 1', 'selector' => '#billing_address_1_field, #shipping_address_1_field'],
    'address_2'  => ['title' => 'Address 2', 'selector' => '#billing_address_2_field, #shipping_address_2_field'],
    'phone'      => ['title' => 'Phone', 'selector' => '#billing_phone_field'],
    'email'      => ['title' => 'Email', 'selector' => '#billing_email_field'],
    'company'    => ['title' => 'Company', 'selector' => '#billing_company_field'],
    'notes'     => ['title' => 'Notes', 'selector' => '#order_comments_field'],
];
