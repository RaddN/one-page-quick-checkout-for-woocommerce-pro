<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// cart-template.php

function onepaqucpro_cart_drawer_variation_in_title_enabled()
{
    return onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_variation_in_title', '1');
}

function onepaqucpro_get_cart_drawer_item_title($cart_item, $product, $show_variation_in_title)
{
    if ($show_variation_in_title || empty($cart_item['variation_id'])) {
        return $product ? $product->get_name() : '';
    }

    $parent_product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    $parent_product = $parent_product_id > 0 && function_exists('wc_get_product') ? wc_get_product($parent_product_id) : false;

    return $parent_product ? $parent_product->get_name() : ($product ? $product->get_name() : '');
}

function onepaqucpro_get_cart_drawer_variation_meta_rows($cart_item)
{
    $cart_item = is_array($cart_item) ? $cart_item : array();
    $variation_id = !empty($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;

    if ($variation_id <= 0) {
        return array();
    }

    $parent_product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    $parent_product = $parent_product_id > 0 && function_exists('wc_get_product') ? wc_get_product($parent_product_id) : null;
    $variation_attributes = !empty($cart_item['variation']) && is_array($cart_item['variation']) ? $cart_item['variation'] : array();

    if (empty($variation_attributes) && function_exists('wc_get_product')) {
        $variation_product = wc_get_product($variation_id);
        if ($variation_product && method_exists($variation_product, 'get_variation_attributes')) {
            $variation_attributes = (array) $variation_product->get_variation_attributes();
        }
    }

    $rows = array();
    foreach ($variation_attributes as $attribute_key => $attribute_value) {
        if ($attribute_value === '' || $attribute_value === null) {
            continue;
        }

        $attribute_key = function_exists('onepaqucpro_normalize_attr_key')
            ? onepaqucpro_normalize_attr_key((string) $attribute_key)
            : (string) $attribute_key;

        $label = function_exists('onepaqucpro_get_variation_attribute_taxonomy_label')
            ? onepaqucpro_get_variation_attribute_taxonomy_label($attribute_key, $parent_product)
            : (function_exists('wc_attribute_label') ? wc_attribute_label(str_replace('attribute_', '', $attribute_key), $parent_product) : onepaqucpro_format_floating_cart_meta_label($attribute_key));
        $display = function_exists('onepaqucpro_get_variation_attribute_label')
            ? onepaqucpro_get_variation_attribute_label($attribute_key, $attribute_value, $parent_product)
            : onepaqucpro_get_floating_cart_meta_display_value($attribute_value);

        if ($label === '' || trim(wp_strip_all_tags((string) $display)) === '') {
            continue;
        }

        $rows[] = array(
            'key'     => $label,
            'display' => $display,
        );
    }

    return $rows;
}

function onepaqucpro_get_cart_drawer_variation_meta_output_rows($cart_item, $rule)
{
    $variation_rows = onepaqucpro_get_cart_drawer_variation_meta_rows($cart_item);
    if (empty($variation_rows)) {
        return array();
    }

    $mode = isset($rule['mode']) ? onepaqucpro_sanitize_floating_cart_meta_mode($rule['mode']) : 'separate';
    if ($mode !== 'combine') {
        return $variation_rows;
    }

    $parts = array();
    foreach ($variation_rows as $variation_row) {
        if (empty($variation_row['key']) || !isset($variation_row['display'])) {
            continue;
        }

        $display = trim(wp_strip_all_tags((string) $variation_row['display']));
        if ($display !== '') {
            $parts[] = $display;
        }
    }

    if (empty($parts)) {
        return array();
    }

    return array(
        array(
            'key'     => !empty($rule['title']) ? wp_strip_all_tags((string) $rule['title']) : __('Variations', 'one-page-quick-checkout-for-woocommerce-pro'),
            'display' => implode(', ', $parts),
        ),
    );
}

function onepaqucpro_merge_cart_drawer_meta_rows($leading_rows, $trailing_rows)
{
    $merged = array();
    $seen = array();

    foreach (array_merge((array) $leading_rows, (array) $trailing_rows) as $row) {
        if (empty($row['key']) || !isset($row['display'])) {
            continue;
        }

        $signature = sanitize_title((string) $row['key']) . '|' . md5(trim(wp_strip_all_tags((string) $row['display'])));
        if (isset($seen[$signature])) {
            continue;
        }

        $seen[$signature] = true;
        $merged[] = $row;
    }

    return $merged;
}

function onepaqucpro_render_cart_drawer_item($cart_item_key, $cart_item, $product_title_tag = 'p')
{
    $_product = $cart_item['data'];
    if (!$_product) {
        return;
    }

    $show_item_select = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_item_select');
    $show_remove_item = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_remove_item');
    $show_product_image = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_product_image');
    $show_product_title = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_product_title');
    $show_variation_in_title = onepaqucpro_cart_drawer_variation_in_title_enabled();
    $show_product_price = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_product_price');
    $show_quantity = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_quantity');
    $show_variation_editor = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_variation_editor');
    $show_item_meta = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_item_meta');
    $thumbnail = $_product->get_image();
    $product_price = wc_price($_product->get_price());
    $product_title = onepaqucpro_get_cart_drawer_item_title($cart_item, $_product, $show_variation_in_title);
    $product_quantity = isset($cart_item['quantity']) ? absint($cart_item['quantity']) : 1;
    $variation_editor = $show_variation_editor ? onepaqucpro_get_cart_item_variation_editor_html($cart_item, $cart_item_key, 'drawer') : '';
    $item_meta = $show_item_meta ? onepaqucpro_get_filtered_cart_item_meta($cart_item) : array();
    ?>
    <div class="cart-item<?php echo !$show_item_select ? ' cart-item--no-select' : ''; ?><?php echo (!$show_product_image && !$show_remove_item) ? ' cart-item--no-media' : ''; ?>" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
        <?php if ($show_item_select) : ?>
            <div class="item-select">
                <input type="checkbox" class="item-checkbox" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
            </div>
        <?php endif; ?>
        <?php if ($show_product_image || $show_remove_item) : ?>
            <div class="thumbnail<?php echo !$show_product_image ? ' thumbnail--button-only' : ''; ?>">
                <?php if ($show_product_image) : ?>
                    <?php echo wp_kses($thumbnail, array(
                        'img' => array(
                            'src' => array(),
                            'alt' => array(),
                            'class' => array(),
                        ),
                    )); ?>
                <?php endif; ?>
                <?php if ($show_remove_item) : ?>
                    <button class="remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"><svg style="width: 16px; fill: #ff0000;" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="item-details">
            <?php if ($show_product_title || $show_product_price) : ?>
                <div class="cart-item__summary">
                    <?php if ($show_product_title) : ?>
                        <<?php echo esc_attr($product_title_tag); ?> class="item-title"><?php echo esc_html($product_title); ?></<?php echo esc_attr($product_title_tag); ?>>
                    <?php endif; ?>
                    <?php if ($show_product_price) : ?>
                        <p class="item-price"><?php echo wp_kses_post($product_price); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($item_meta)) : ?>
                <dl class="cart-item-meta">
                    <?php foreach ($item_meta as $meta_row) : ?>
                        <div class="cart-item-meta__row">
                            <dt><?php echo esc_html($meta_row['key']); ?></dt>
                            <dd><?php echo wp_kses_post($meta_row['display']); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
            <?php if ($show_quantity || $variation_editor !== '') : ?>
                <div class="cart-item__actions">
                    <?php if ($show_quantity) : ?>
                        <div class="quantity-controls">
                            <button class="quantity-btn minus" data-action="minus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
                            <input type="number" class="item-quantity" value="<?php echo esc_attr($product_quantity); ?>" min="1" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <button class="quantity-btn plus" data-action="plus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                        </div>
                    <?php endif; ?>
                    <?php if ($variation_editor !== '') : ?>
                        <?php echo $variation_editor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Build inner HTML for cart drawer "You may also like" (simple, purchasable, in-stock products).
 * Returns empty string if there are no suitable related products — do not render the section wrapper in that case.
 *
 * @param int   $parent_product_id Product ID whose related products are loaded.
 * @param int   $max_products      Maximum cards to output.
 * @param int[] $exclude_ids       Product IDs to skip (e.g. line items already in cart).
 * @return string Trimmed HTML fragment or empty string.
 */
function onepaqucpro_cart_drawer_get_you_may_also_like_html($parent_product_id, $max_products = 3, $exclude_ids = array())
{
    $parent_product_id = absint($parent_product_id);
    if ($parent_product_id < 1) {
        return '';
    }

    $exclude_ids = array_filter(array_map('absint', (array) $exclude_ids));
    $fetch_limit = max(15, (int) $max_products * 5);
    $related_ids = wc_get_related_products($parent_product_id, $fetch_limit);

    if (empty($related_ids) || !is_array($related_ids)) {
        return '';
    }

    ob_start();
    $shown = 0;
    foreach ($related_ids as $rid) {
        if ($shown >= $max_products) {
            break;
        }
        $rid = absint($rid);
        if ($rid < 1 || in_array($rid, $exclude_ids, true)) {
            continue;
        }

        $product = wc_get_product($rid);
        if (!$product || $product->get_type() !== 'simple' || !$product->is_purchasable() || !$product->is_in_stock()) {
            continue;
        }

        echo '<div class="recommended-product">';
        echo '<a href="' . esc_url($product->get_permalink()) . '">';
        echo wp_kses_post($product->get_image());
        echo '<h4>' . esc_html($product->get_name()) . '</h4>';
        echo '<span class="price">' . wp_kses_post($product->get_price_html()) . '</span>';
        echo '</a>';
        $button_text = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_related_add_to_cart_text', $product->add_to_cart_text());
        echo '<button type="button" class="add-to-cart-button" data-product-id="' . esc_attr($product->get_id()) . '">' . esc_html($button_text) . '</button>';
        echo '</div>';

        $shown++;
    }

    return trim(ob_get_clean());
}

function onepaqucpro_get_cart_contents_count()
{
    if (!function_exists('WC') || !WC() || !WC()->cart) {
        return 0;
    }

    return (int) WC()->cart->get_cart_contents_count();
}

function onepaqucpro_hide_empty_cart_button_enabled()
{
    if (!function_exists('onepaqucpro_premium_feature') || !onepaqucpro_premium_feature()) {
        return false;
    }

    return get_option('rmenu_hide_empty_cart_button', '0') === '1';
}

function onepaqucpro_floating_cart_meta_row_matches_rule($row, $rule)
{
    if (!is_array($rule) || empty($rule['key'])) {
        return false;
    }

    return onepaqucpro_floating_cart_meta_row_matches($row, onepaqucpro_get_floating_cart_meta_aliases($rule['key']));
}

function onepaqucpro_get_floating_cart_meta_row_output($row, $rule = null)
{
    $label = isset($row['label']) ? wp_strip_all_tags((string) $row['label']) : '';
    $display = isset($row['display']) ? (string) $row['display'] : '';

    if (is_array($rule) && !empty($rule['title'])) {
        $label = wp_strip_all_tags((string) $rule['title']);
    }

    if ($label === '' || trim(wp_strip_all_tags($display)) === '') {
        return null;
    }

    return array(
        'key' => $label,
        'display' => $display,
    );
}

function onepaqucpro_get_filtered_cart_item_meta($cart_item)
{
    if (!function_exists('onepaqucpro_get_floating_cart_cart_item_meta_rows')) {
        return array();
    }

    $include = onepaqucpro_get_floating_cart_meta_rules_option('rmenu_floating_cart_meta_include');
    $filtered = array();

    if (empty($include)) {
        return array();
    }

    $rows = onepaqucpro_get_floating_cart_cart_item_meta_rows($cart_item);
    $rows = is_array($rows) ? $rows : array();
    $used_rows = array();
    foreach ($include as $rule) {
        if (function_exists('onepaqucpro_is_floating_cart_variations_meta_key') && onepaqucpro_is_floating_cart_variations_meta_key($rule['key'])) {
            if (!onepaqucpro_cart_drawer_variation_in_title_enabled()) {
                $filtered = onepaqucpro_merge_cart_drawer_meta_rows(
                    $filtered,
                    onepaqucpro_get_cart_drawer_variation_meta_output_rows($cart_item, $rule)
                );
            }

            continue;
        }

        foreach ($rows as $row_index => $row) {
            if (isset($used_rows[$row_index])) {
                continue;
            }

            if (!onepaqucpro_floating_cart_meta_row_matches_rule($row, $rule)) {
                continue;
            }

            $output = onepaqucpro_get_floating_cart_meta_row_output($row, $rule);
            if ($output !== null) {
                $filtered[] = $output;
                $used_rows[$row_index] = true;
            }

            break;
        }
    }

    return onepaqucpro_merge_cart_drawer_meta_rows(array(), $filtered);
}

function onepaqucpro_get_cart_item_group_label($cart_item)
{
    $group_by = get_option('rmenu_floating_cart_group_by', 'category');
    $product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;

    if ($group_by === 'brand') {
        foreach (array('product_brand', 'pa_brand') as $taxonomy) {
            if (taxonomy_exists($taxonomy)) {
                $terms = get_the_terms($product_id, $taxonomy);
                if (!empty($terms) && !is_wp_error($terms)) {
                    return $terms[0]->name;
                }
            }
        }

        return __('No brand', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    if ($group_by === 'meta') {
        $meta_key = get_option('rmenu_floating_cart_group_meta_key', '');
        if ($meta_key !== '' && function_exists('onepaqucpro_get_floating_cart_cart_item_meta_value')) {
            $meta_value = onepaqucpro_get_floating_cart_cart_item_meta_value($cart_item, $meta_key);
            if ($meta_value !== '') {
                return $meta_value;
            }
        }

        return __('Other', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    $terms = get_the_terms($product_id, 'product_cat');
    if (!empty($terms) && !is_wp_error($terms)) {
        return $terms[0]->name;
    }

    return __('Uncategorized', 'one-page-quick-checkout-for-woocommerce-pro');
}

function onepaqucpro_get_cart_item_group_icon()
{
    $icon = get_option('rmenu_floating_cart_group_icon', 'tag');
    $icons = array(
        'none' => '',
        'tag' => '&#9679;',
        'folder' => '&#9632;',
        'star' => '&#9733;',
        'location' => '<svg class="cart-item-group__icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
    );

    return isset($icons[$icon]) ? $icons[$icon] : $icons['tag'];
}

function onepaqucpro_get_cart_item_group_icon_allowed_html()
{
    return array(
        'svg' => array(
            'class' => true,
            'xmlns' => true,
            'width' => true,
            'height' => true,
            'viewBox' => true,
            'viewbox' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
            'aria-hidden' => true,
            'focusable' => true,
        ),
        'path' => array(
            'd' => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r' => true,
        ),
    );
}

function onepaqucpro_render_grouped_cart_drawer_items($cart_items, $product_title_tag)
{
    if (!onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_group_items', '0')) {
        foreach ($cart_items as $cart_item_key => $cart_item) {
            onepaqucpro_render_cart_drawer_item($cart_item_key, $cart_item, $product_title_tag);
        }
        return;
    }

    $groups = array();
    foreach ($cart_items as $cart_item_key => $cart_item) {
        $label = onepaqucpro_get_cart_item_group_label($cart_item);
        $groups[$label][$cart_item_key] = $cart_item;
    }

    foreach ($groups as $label => $group_items) {
        $icon = onepaqucpro_get_cart_item_group_icon();
        echo '<div class="cart-item-group">';
        echo '<h4 class="cart-item-group__title">';
        if ($icon !== '') {
            echo '<span class="cart-item-group__icon">' . wp_kses($icon, onepaqucpro_get_cart_item_group_icon_allowed_html()) . '</span>';
        }
        echo esc_html($label) . '</h4>';
        foreach ($group_items as $cart_item_key => $cart_item) {
            onepaqucpro_render_cart_drawer_item($cart_item_key, $cart_item, $product_title_tag);
        }
        echo '</div>';
    }
}

function onepaqucpro_render_floating_cart_shipping_options()
{
    if (!WC()->cart->needs_shipping() || !WC()->shipping()) {
        return '';
    }

    WC()->cart->calculate_shipping();
    $packages = WC()->shipping()->get_packages();
    if (empty($packages)) {
        return '';
    }

    ob_start();
    foreach ($packages as $package_key => $package) {
        if (empty($package['rates'])) {
            continue;
        }

        $chosen_method = WC()->session ? WC()->session->get('chosen_shipping_methods') : array();
        echo '<ul class="floating-cart-shipping-methods">';
        foreach ($package['rates'] as $rate_id => $rate) {
            $selected = isset($chosen_method[$package_key]) ? $chosen_method[$package_key] : '';
            echo '<li>';
            echo '<label>';
            echo '<input type="radio" class="shipping_method" name="shipping_method[' . esc_attr($package_key) . ']" value="' . esc_attr($rate_id) . '" ' . checked($selected, $rate_id, false) . '>';
            echo wp_kses_post(wc_cart_totals_shipping_method_label($rate));
            echo '</label>';
            echo '</li>';
        }
        echo '</ul>';
    }

    return trim(ob_get_clean());
}

// Shortcode to display cart icon and drawer
function onepaqucpro_cart($drawer_position = 'right', $cart_icon = 'cart', $product_title_tag = 'p', $position = "", $top = "", $left = "")
{
    $cart_icons = array(
        'cart' => '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 1.95 1.95" enable-background="new 0 0 52 52" xml:space="preserve"><g><path d="M0.754 0.975H1.65c0.026 0 0.052 -0.019 0.056 -0.045l0.165 -0.578c0.011 -0.041 -0.019 -0.075 -0.056 -0.075H0.431l-0.022 -0.086C0.397 0.15 0.36 0.124 0.322 0.124h-0.15c-0.049 0 -0.094 0.037 -0.098 0.086C0.071 0.263 0.116 0.307 0.165 0.307h0.086l0.285 0.964c0.011 0.041 0.045 0.068 0.086 0.068h1.057c0.049 0 0.094 -0.037 0.098 -0.086 0.004 -0.052 -0.041 -0.098 -0.09 -0.098H0.757c-0.041 0 -0.075 -0.026 -0.086 -0.064V1.087c-0.019 -0.056 0.026 -0.112 0.083 -0.112"/><path cx="20.6" cy="44.6" r="4" d="M0.922 1.673A0.15 0.15 0 0 1 0.773 1.823A0.15 0.15 0 0 1 0.623 1.673A0.15 0.15 0 0 1 0.922 1.673z"/><path cx="40.1" cy="44.6" r="4" d="M1.654 1.673A0.15 0.15 0 0 1 1.504 1.823A0.15 0.15 0 0 1 1.354 1.673A0.15 0.15 0 0 1 1.654 1.673z"/></g></svg>',

        'shopping-bag' => '<svg fill="#fff" height="30px" width="30px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 19.2 19.2" enable-background="new 0 0 512 512" xml:space="preserve"><path d="M15.795 4.8h-1.601v0.799c0 0.881 -0.716 1.601 -1.601 1.601 -0.881 0 -1.601 -0.716 -1.601 -1.601V4.8h-3.199v0.799c0 0.881 -0.716 1.601 -1.601 1.601 -0.881 0 -1.601 -0.716 -1.601 -1.601V4.8H2.996c0 7.999 -0.799 14.4 -0.799 14.4h14.4c-0.004 0 -0.802 -6.401 -0.802 -14.4m-9.6 1.601c0.443 0 0.799 -0.356 0.799 -0.799v-1.601c0 -1.327 1.073 -2.4 2.4 -2.4s2.4 1.073 2.4 2.4v1.601c0 0.443 0.356 0.799 0.799 0.799s0.799 -0.356 0.799 -0.799v-1.601C13.395 1.792 11.602 0 9.394 0S5.393 1.792 5.393 4.001v1.601c0.004 0.439 0.36 0.799 0.802 0.799"/></svg>',

        'basket' => '<svg fill="#fff" height="30" width="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.2 19.2" xml:space="preserve"><path d="M15.199 7.2 12 0h-1.601l3.199 7.2zM8.801 0H7.2L4.001 7.2h1.601zm-7.2 17.599c0 .881.716 1.601 1.601 1.601h12.799c.881 0 1.601-.716 1.601-1.601l.799-7.2H.799zm12-5.599h1.601l-.802 5.599h-1.601zm-4.8 0h1.601v5.599H8.801zm-3.203 0 .799 5.599H4.8L4.001 12zM18.4 7.999H.799A.8.8 0 0 0 0 8.801V9.6h19.2v-.799a.8.8 0 0 0-.799-.802"/></svg>'
    );

    // Get selected cart icon or fallback to default
    $selected_icon = isset($cart_icons[$cart_icon]) ? $cart_icons[$cart_icon] : $cart_icons['cart'];
    $cart_count = onepaqucpro_get_cart_contents_count();
    $hide_empty_cart_button = onepaqucpro_hide_empty_cart_button_enabled();
    $hide_cart_button_now = $hide_empty_cart_button && $cart_count < 1;
    $show_cart_icon = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_cart_icon');
    $show_cart_count = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_cart_count');
    $show_empty_icon = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_empty_icon');
    $empty_cart_icon = function_exists('onepaqucpro_get_floating_cart_empty_icon') ? onepaqucpro_get_floating_cart_empty_icon() : 'cart';
    $show_shop_button = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_shop_button');
    $show_item_select = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_item_select');
    $show_select_bar = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_select_bar') && $show_item_select;
    $show_coupon = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_coupon');
    $show_recommendations = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_recommendations');
    $show_summary = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_summary');
    $summary_collapsible = $show_summary && onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_summary_collapsible', '0');
    $summary_initially_collapsed = $summary_collapsible && onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_summary_initially_collapsed', '0');
    $show_subtotal = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_subtotal');
    $show_discount = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_discount');
    $show_shipping_options = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_shipping_options', '0');
    $show_shipping_total = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_shipping_total');
    $show_tax_total = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_tax_total');
    $show_total = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_total');
    $show_checkout = onepaqucpro_floating_cart_element_enabled('rmenu_floating_cart_show_checkout');
    $cart_title = onepaqucpro_get_floating_cart_text('your_cart', __('Your Cart', 'one-page-quick-checkout-for-woocommerce-pro'));
    $empty_cart_title = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_empty_title', __('Your Cart is Empty', 'one-page-quick-checkout-for-woocommerce-pro'));
    $shop_button_text = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_shop_button_text', __('Shop Now', 'one-page-quick-checkout-for-woocommerce-pro'));
    $select_all_label = onepaqucpro_get_floating_cart_text('txt_Select_All', __('Select All', 'one-page-quick-checkout-for-woocommerce-pro'));
    $selected_suffix = onepaqucpro_get_floating_cart_selected_suffix();
    $coupon_title = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_coupon_title', __('Have a coupon?', 'one-page-quick-checkout-for-woocommerce-pro'));
    $coupon_placeholder = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_coupon_placeholder', __('Enter coupon code', 'one-page-quick-checkout-for-woocommerce-pro'));
    $coupon_button_text = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_coupon_button_text', __('Apply', 'one-page-quick-checkout-for-woocommerce-pro'));
    $applied_coupons_heading = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_applied_coupons_heading', __('Applied Coupons:', 'one-page-quick-checkout-for-woocommerce-pro'));
    $remove_coupon_text = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_remove_coupon_text', __('Remove', 'one-page-quick-checkout-for-woocommerce-pro'));
    $you_may_like_label = onepaqucpro_get_floating_cart_text('txt_you_may_like', __('You may also like', 'one-page-quick-checkout-for-woocommerce-pro'));
    $subtotal_label = onepaqucpro_get_floating_cart_text('txt_subtotal', __('Subtotal', 'one-page-quick-checkout-for-woocommerce-pro'));
    $discount_label = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_discount_label', __('Discount', 'one-page-quick-checkout-for-woocommerce-pro'));
    $shipping_options_label = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_shipping_options_label', __('Shipping options', 'one-page-quick-checkout-for-woocommerce-pro'));
    $shipping_label = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_shipping_label', __('Shipping', 'one-page-quick-checkout-for-woocommerce-pro'));
    $tax_label = onepaqucpro_get_floating_cart_text('rmenu_floating_cart_tax_label', __('Tax', 'one-page-quick-checkout-for-woocommerce-pro'));
    $total_label = onepaqucpro_get_floating_cart_text('txt_total', __('Total', 'one-page-quick-checkout-for-woocommerce-pro'));
    $checkout_label = onepaqucpro_get_floating_cart_text('txt_checkout', __('Checkout', 'one-page-quick-checkout-for-woocommerce-pro'));
    $cart_button_classes = array(
        'rwc_cart-button',
        'plugincy_pos_' . sanitize_html_class($position),
    );

    if ($hide_cart_button_now) {
        $cart_button_classes[] = 'onepaqucpro-cart-button-hidden';
    }

    $allowed_svg = function_exists('onepaqucpro_get_floating_cart_svg_allowed_html') ? onepaqucpro_get_floating_cart_svg_allowed_html() : array(
        'svg' => array('xmlns' => array(), 'viewBox' => array(), 'viewbox' => array(), 'width' => array(), 'height' => array(), 'role' => array(), 'aria-hidden' => array(), 'aria-label' => array(), 'style' => array(), 'class' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array()),
        'path' => array('d' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array()),
        'circle' => array('cx' => array(), 'cy' => array(), 'r' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array()),
    );
?>

    <button type="button" class="<?php echo esc_attr(implode(' ', $cart_button_classes)); ?>" aria-label="<?php echo esc_attr($cart_title); ?>" data-cart-icon="<?php echo esc_attr($cart_icon); ?>" data-product_title_tag="<?php echo esc_attr($product_title_tag); ?>" data-drawer-position="<?php echo esc_attr($drawer_position); ?>" data-hide-empty-cart-button="<?php echo esc_attr($hide_empty_cart_button ? '1' : '0'); ?>" data-cart-count="<?php echo esc_attr($cart_count); ?>" onclick="openCartDrawer('<?php echo esc_attr($drawer_position); ?>')" <?php echo $hide_cart_button_now ? 'hidden aria-hidden="true" tabindex="-1"' : ''; ?>>
        <?php if ($show_cart_icon) : ?>
            <span class="cart-icon">
                <?php echo wp_kses($selected_icon, $allowed_svg); ?>
            </span>
        <?php endif; ?>
        <?php if ($show_cart_count) : ?>
            <span class="cart-count">
                <?php echo esc_html($cart_count); ?>
            </span>
        <?php endif; ?>
    </button>
    <div class="cart-drawer <?php echo esc_attr($drawer_position); ?>">
        <div class="cart-content">
            <div class="cart-header">
                <h2><?php echo esc_html($cart_title); ?></h2>
                <button class="close_button" onclick="closeCheckoutPopup()"></button>
            </div>
            <?php
            if (function_exists('WC') && WC() && WC()->cart) {
                if (WC()->cart->is_empty()) {
            ?>
                    <div class="cart-items empty-cart-items">
                        <div class="empty-cart">
                            <?php if ($show_empty_icon) : ?>
                                <span class="plugincy-empty-cart-icon">
                                    <?php echo wp_kses(onepaqucpro_get_floating_cart_empty_icon_svg($empty_cart_icon), $allowed_svg); ?>
                                </span>
                            <?php endif; ?>
                            <div class="plugincy-zero-state-title"><?php echo esc_html($empty_cart_title); ?></div>
                            <?php
                            // Get the shop page URL or fallback to home page
                            $shop_url = get_home_url(); // Default to home page

                            // Check if WooCommerce is active and get shop page ID
                            if (function_exists('wc_get_page_id')) {
                                $shop_page_id = wc_get_page_id('shop');

                                // If shop page exists and is published, use its URL
                                if ($shop_page_id && get_post_status($shop_page_id) === 'publish') {
                                    $shop_url = get_permalink($shop_page_id);
                                }
                            }
                            // Alternative check if WooCommerce functions aren't available
                            elseif (function_exists('get_option')) {
                                $shop_page_id = get_option('woocommerce_shop_page_id');

                                // If shop page exists and is published, use its URL
                                if ($shop_page_id && get_post_status($shop_page_id) === 'publish') {
                                    $shop_url = get_permalink($shop_page_id);
                                }
                            }
                            ?>

                            <?php if ($show_shop_button) : ?>
                                <a href="<?php echo esc_url($shop_url); ?>" class="plugincy-primary-button plugincy-shop-button plugincy-modal-close"><?php echo esc_html($shop_button_text); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                } else {
                    $cart_count = WC()->cart->get_cart_contents_count();
                    $cart_items = WC()->cart->get_cart();
                ?>
                    <?php if ($show_select_bar) : ?>
                        <div class="cart-selection-bar">
                            <div class="select-all-container">
                                <input type="checkbox" id="select-all-items" class="select-all-checkbox">
                                <label for="select-all-items"><?php echo esc_html($select_all_label); ?></label>
                            </div>
                            <div class="selected-count">
                                <span id="selected-count-text">0 <?php echo esc_html($selected_suffix); ?></span>
                                <button id="remove-selected" class="remove-selected-button" style="display:none;"><svg style="width: 16px; fill: #ffffff;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label="">
                                        <path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path>
                                    </svg></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="cart-items">
                        <?php
                        onepaqucpro_render_grouped_cart_drawer_items($cart_items, $product_title_tag);
                        ?>
                    </div>
                    <!-- Coupon Section -->
                    <?php if ($show_coupon) : ?>
                        <div class="coupon-section">
                            <?php if ($coupon_title !== '') : ?>
                                <h4 class="coupon-section-title"><?php echo esc_html($coupon_title); ?></h4>
                            <?php endif; ?>
                            <div class="coupon-form">
                                <input type="text" id="coupon-code" placeholder="<?php echo esc_attr($coupon_placeholder); ?>" class="coupon-input">
                                <button id="apply-coupon" class="apply-coupon-button"><?php echo esc_html($coupon_button_text); ?></button>
                            </div>
                            <div id="coupon-message" class="coupon-message" style="display: none;"></div>
                            <div id="applied-coupons" class="applied-coupons" style="display: <?php echo WC()->cart->get_applied_coupons() ? "block" : "none"; ?>;">
                                <?php
                                if (WC()->cart->get_applied_coupons()) {
                                    echo '<h4>' . esc_html($applied_coupons_heading) . '</h4>';
                                    foreach (WC()->cart->get_applied_coupons() as $code) {
                                        echo '<div class="applied-coupon">';
                                        echo '<span>' . esc_html($code) . '</span>';
                                        echo '<button class="remove-coupon" data-coupon="' . esc_attr($code) . '">' . esc_html($remove_coupon_text) . '</button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- You May Also Like Section (if only one product in cart and recommendations exist) -->
                    <?php
                    $onepaqucpro_ymal_inner = '';
                    if ($show_recommendations && (int) $cart_count === 1 && !empty($cart_items) && is_array($cart_items)) {
                        $cart_product = reset($cart_items);
                        $ymal_parent_id = isset($cart_product['product_id']) ? absint($cart_product['product_id']) : 0;
                        if ($ymal_parent_id > 0) {
                            $exclude_in_cart = array($ymal_parent_id);
                            $onepaqucpro_ymal_inner = onepaqucpro_cart_drawer_get_you_may_also_like_html($ymal_parent_id, 3, $exclude_in_cart);
                        }
                    }
                    ?>
                    <?php if ($onepaqucpro_ymal_inner !== '') : ?>
                        <div class="you-may-also-like">
                            <h3><?php echo esc_html($you_may_like_label); ?></h3>
                            <div class="recommended-products">
                                <?php echo $onepaqucpro_ymal_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same structured markup as product loop above. ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cart Summary -->
                    <?php $shipping_options_html = $show_shipping_options ? onepaqucpro_render_floating_cart_shipping_options() : ''; ?>
                    <?php if ($shipping_options_html !== '') : ?>
                            <div class="floating-cart-shipping-options">
                                <h4><?php echo esc_html($shipping_options_label); ?></h4>
                                <?php echo $shipping_options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                    <?php endif; ?>

                    <?php if ($show_summary && ($show_subtotal || $show_discount || $show_shipping_total || $show_tax_total || $show_total)) : ?>
                        <?php
                        $summary_has_discount_row = $show_discount && WC()->cart->get_discount_total() > 0;
                        $summary_has_shipping_row = $show_shipping_total && WC()->cart->needs_shipping();
                        $summary_has_tax_row = $show_tax_total && WC()->cart->get_total_tax() > 0;
                        $summary_has_detail_rows = $show_subtotal || $summary_has_discount_row || $summary_has_shipping_row || $summary_has_tax_row;
                        $summary_collapsible = $summary_collapsible && $show_total && $summary_has_detail_rows;
                        $summary_dropup = $summary_collapsible && (int) $cart_count === 1 && $onepaqucpro_ymal_inner !== '' && $shipping_options_html !== '';
                        $summary_classes = array('cart-summary');
                        if ($summary_collapsible) {
                            $summary_classes[] = 'cart-summary--collapsible';
                            if ($summary_dropup) {
                                $summary_classes[] = 'cart-summary--dropup';
                            }
                            if ($summary_initially_collapsed) {
                                $summary_classes[] = 'is-collapsed';
                            }
                        }
                        $summary_content_id = function_exists('wp_unique_id') ? wp_unique_id('onepaqucpro-cart-summary-') : 'onepaqucpro-cart-summary-' . wp_rand();
                        $summary_total_html = wc_price(WC()->cart->get_total('raw'));
                        ?>
                        <div class="<?php echo esc_attr(implode(' ', $summary_classes)); ?>">
                            <?php if ($summary_collapsible) : ?>
                                <div id="<?php echo esc_attr($summary_content_id); ?>" class="cart-summary__content" aria-hidden="<?php echo $summary_initially_collapsed ? 'true' : 'false'; ?>">
                            <?php endif; ?>
                            <?php if ($show_subtotal) : ?>
                                <div class="summary-row">
                                    <span><?php echo esc_html($subtotal_label); ?></span>
                                    <span><?php echo wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($summary_has_discount_row) : ?>
                                <div class="summary-row discount">
                                    <span><?php echo esc_html($discount_label); ?></span>
                                    <span>- <?php echo wp_kses_post(wc_price(WC()->cart->get_discount_total())); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($summary_has_shipping_row) : ?>
                                <div class="summary-row shipping">
                                    <span><?php echo esc_html($shipping_label); ?></span>
                                    <span><?php echo wp_kses_post(WC()->cart->get_cart_shipping_total()); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($summary_has_tax_row) : ?>
                                <div class="summary-row tax">
                                    <span><?php echo esc_html($tax_label); ?></span>
                                    <span><?php echo wp_kses_post(wc_price(WC()->cart->get_total_tax())); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($summary_collapsible) : ?>
                                </div>
                                <button type="button" class="cart-summary-toggle summary-row total" aria-expanded="<?php echo $summary_initially_collapsed ? 'false' : 'true'; ?>" aria-controls="<?php echo esc_attr($summary_content_id); ?>" aria-label="<?php esc_attr_e('Toggle cart summary', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                    <span><?php echo esc_html($total_label); ?></span>
                                    <span class="cart-summary-toggle__amount"><?php echo wp_kses_post($summary_total_html); ?></span>
                                    <span class="cart-summary-toggle__icon" aria-hidden="true"></span>
                                </button>
                            <?php elseif ($show_total) : ?>
                                <div class="summary-row total">
                                    <span><?php echo esc_html($total_label); ?></span>
                                    <span><?php echo wp_kses_post($summary_total_html); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Checkout Button -->
                    <?php if ($show_checkout) : ?>
                        <div class="cart-actions">
                            <a style="display: none;flex-direction: column;justify-content: center;align-items: center;" class="checkout-button checkout-button-drawer-link"><?php echo esc_html($checkout_label); ?></a>
                            <?php $rmenu_cart_checkout_behavior = function_exists('onepaqucpro_get_floating_cart_checkout_behavior') ? onepaqucpro_get_floating_cart_checkout_behavior() : 'direct_checkout';
                            if ($rmenu_cart_checkout_behavior === 'popup_checkout') {
                            ?>
                                <button class="checkout-button checkout-button-drawer" onclick="openCheckoutPopup()">
                                    <?php echo esc_html($checkout_label); ?>
                                </button>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button checkout-button-drawer">
                                    <?php echo esc_html($checkout_label); ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php endif; ?>
            <?php }
            } else {
                // Fallback when WooCommerce is not initialized
                echo '<p>' . esc_html__('Your cart is currently empty.', 'one-page-quick-checkout-for-woocommerce-pro') . '</p>';
            } ?>
        </div>
    </div>
    <div class="overlay"></div>

    <?php if (get_option("rmenu_enable_sticky_cart", 0)) : ?>
        <style>
            :root {
                <?php
                $border_radius = get_option('rmenu_cart_border_radius', '5px 0 0 5px');
                $top_position = get_option('rmenu_cart_top_position', '50%');
                $left_position = get_option('rmenu_cart_left_position', '100%');

                // Check if border_radius has a unit
                if (!preg_match('/(px|%|em|rem|vw|vh)$/', $border_radius)) {
                    $border_radius .= 'px'; // Append px if no unit is present
                }
                if (!preg_match('/(px|%|em|rem|vw|vh)$/', $top_position)) {
                    $top_position .= 'px'; // Append px if no unit is present
                }
                if (!preg_match('/(px|%|em|rem|vw|vh)$/', $left_position)) {
                    $left_position .= 'px'; // Append px if no unit is present
                }

                // Convert border_radius to an integer for comparison
                $border_radius_value = intval($border_radius); // Get the numeric value

                if ($border_radius_value >= 50) { // Check if the value is greater than or equal to 50
                    echo '--cart-radius: 50%;';
                    echo '--cart-width: 50px;';
                    echo '--cart-height: 50px;';
                    echo '--cart-padding: 0;';
                } else {
                    echo '--cart-radius: ' . esc_attr($border_radius) . ';';
                    echo '--cart-width: auto;';
                    echo '--cart-height: auto;';
                    echo '--cart-padding: 15px;';
                }
                ?>
                --cart-top: <?php echo esc_attr($top_position); ?>;
                --cart-left: <?php echo esc_attr($left_position); ?>;
                --cart-bg: <?php echo esc_attr(get_option('rmenu_cart_bg_color', '#96588a')); ?>;
                --cart-text: <?php echo esc_attr(get_option('rmenu_cart_text_color', '#ffffff')); ?>;
                --cart-hover-bg: <?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>;
                --cart-hover-text: <?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>;

                /* New variables for cart drawer styling */
                --primary-color: <?php echo esc_attr(get_option('rmenu_primary_color', '#4a90e2')); ?>;
                --secondary-color: <?php echo esc_attr(get_option('rmenu_secondary_color', '#f8f8f8')); ?>;
                --text-color: <?php echo esc_attr(get_option('rmenu_text_color', '#333333')); ?>;
                --border-color: <?php echo esc_attr(get_option('rmenu_border_color', '#e1e1e1')); ?>;
                --success-color: <?php echo esc_attr(get_option('rmenu_success_color', '#4caf50')); ?>;
                --danger-color: <?php echo esc_attr(get_option('rmenu_danger_color', '#f44336')); ?>;
            }

            /* Cart Button Styles */
            .plugincy_pos_,
            .plugincy_pos_fixed {
                position: fixed;
                top: var(--cart-top);
                left: var(--cart-left);
                border-radius: var(--cart-radius);
                background: var(--cart-bg);
                color: var(--cart-text);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                width: var(--cart-width);
                height: var(--cart-height);
                padding: var(--cart-padding);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 999;
                transform: translateX(-100%);
            }

            .plugincy_pos_:hover,
            .plugincy_pos_fixed:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
                transform: translate(-100%, -2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            }

            .plugincy_pos_ .cart-icon svg,
            .plugincy_pos_fixed .cart-icon svg {
                fill: var(--cart-text);
                transition: fill 0.3s ease;
                width: 24px;
                height: 24px;
            }

            .plugincy_pos_:hover .cart-icon svg,
            .plugincy_pos_fixed:hover .cart-icon svg {
                fill: var(--cart-hover-text);
            }

            .cart-icon {
                margin-right: <?php echo ($border_radius == '50') ? '0' : '8px'; ?>;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            span.cart-count {
                position: absolute;
                top: <?php echo ($border_radius == '50') ? '-8px' : '-5px'; ?>;
                <?php
                echo ($border_radius == '50') ? 'right: -8px; left: auto;' : 'left: -6px;'; ?>padding: 0;
                border-radius: 50%;
                background: #ff4757;
                color: white;
                font-size: 12px;
                font-weight: bold;
                min-width: 20px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                width: max-content;
                height: 20px;
                box-sizing: border-box;
            }

            /* Cart Drawer Styles */
            .cart-drawer {
                position: fixed;
                top: 0;
                width: 100%;
                max-width: 450px;
                height: 100%;
                background: #fff;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                z-index: 999999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                display: flex;
                flex-direction: column;
                padding-bottom: 20px;
                padding-top: 10px;
            }

            .cart-drawer.right {
                right: 0;
                left: auto;
                transform: translateX(100%);
            }

            .cart-drawer.left {
                left: 0;
                right: auto;
                transform: translateX(-100%);
            }

            .cart-drawer .cart-content {
                overflow-y: auto;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .cart-drawer .cart-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid var(--border-color);
            }

            .cart-drawer .cart-header h2 {
                margin: 0;
                font-size: 24px;
                color: var(--text-color);
            }

            .cart-drawer .close_button {
                background: none;
                border: none;
                cursor: pointer;
                color: var(--text-color);
                padding: 5px;
                border-radius: 50%;
                transition: background-color 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
            }

            .cart-drawer .close_button:hover {
                background-color: var(--secondary-color);
            }

            .cart-drawer .cart-selection-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding: 10px;
                background-color: var(--secondary-color);
                border-radius: 8px;
            }

            .cart-drawer .select-all-container {
                display: flex;
                align-items: center;
            }

            .cart-drawer .select-all-container input {
                margin-right: 8px;
            }

            .cart-drawer .selected-count {
                display: flex;
                align-items: center;
            }

            .cart-drawer .remove-selected-button {
                background: var(--danger-color);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                margin-left: 10px;
                cursor: pointer;
                font-size: 12px;
            }

            .cart-drawer .cart-items {
                margin-bottom: 20px;
                padding: 0 9px;
                min-height: 130px;
            }

            .cart-drawer .cart-item-group {
                margin-bottom: 14px;
            }

            .cart-drawer .cart-item-group__title {
                display: flex;
                align-items: center;
                gap: 7px;
                margin: 0;
                padding: 10px 0 2px;
                color: var(--text-color);
                font-size: 14px;
                font-weight: 700;
            }

            .cart-drawer .cart-item-group__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--cart-bg);
                line-height: 1;
            }

            .cart-drawer .cart-item-group__icon-svg {
                display: block;
                width: 15px;
                height: 15px;
            }

            .cart-drawer .cart-item {
                display: flex;
                align-items: flex-start;
                padding: 1rem 0;
                border-bottom: 1px solid var(--border-color);
            }

            .cart-drawer .item-select {
                padding-top: 5px;
            }

            .cart-drawer .thumbnail {
                width: 80px;
                height: 80px;
                flex-shrink: 0;
                position: relative;
                border: 1px solid var(--border-color);
                border-radius: 7px;
            }

            .cart-drawer .thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 6px;
            }

            .cart-drawer .item-details {
                flex: 1;
            }

            .cart-drawer .item-title {
                margin: 0 0 5px;
                font-size: 16px;
                color: var(--text-color);
            }

            .cart-drawer .item-price {
                margin: 0 0 10px;
                color: var(--cart-bg);
                font-weight: 500;
            }

            .cart-drawer .cart-item-meta {
                display: grid;
                gap: 4px;
                margin: 0 0 10px;
                color: var(--text-color);
                font-size: 12px;
            }

            .cart-drawer .cart-item-meta__row {
                display: flex;
                gap: 6px;
                line-height: 1.35;
            }

            .cart-drawer .cart-item-meta dt {
                font-weight: 700;
            }

            .cart-drawer .cart-item-meta dt::after {
                content: ":";
            }

            .cart-drawer .cart-item-meta dd {
                margin: 0;
            }

            .cart-drawer .quantity-controls {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            .cart-drawer .quantity-btn {
                width: 28px;
                height: 28px;
                border: 1px solid var(--border-color);
                background: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                display: flex !important;
                align-items: center;
                justify-content: center;
                visibility: visible !important;
                padding: 10px;
                color: var(--text-color);
            }

            .cart-drawer .quantity-btn:focus {
                color: var(--text-color);
            }

            .cart-drawer .quantity-btn:hover {
                background: var(--secondary-color);
            }

            .cart-drawer .item-quantity {
                width: 43px;
                text-align: center;
                border: none;
                border-radius: 4px !important;
                margin: 0 -12px 0 3px;
                padding: 0 0px 0 0 !important;
                height: min-content;
            }

            .cart-drawer .item-total {
                font-weight: 600;
                color: var(--text-color);
                margin-bottom: 10px;
            }

            .cart-drawer .remove-item {
                background: #fff;
                border: none;
                color: var(--danger-color);
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                position: absolute;
                top: -8px;
                left: -6px;
                border-radius: 100%;
                padding: 0;
                box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                width: 20px;
                height: 20px;
                justify-content: center;
                z-index: 99999;
            }

            .cart-drawer .coupon-section {
                margin-bottom: 20px;
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
            }

            .cart-drawer .coupon-section-title {
                margin: 0 0 10px;
                color: var(--text-color);
                font-size: 15px;
                font-weight: 700;
            }

            .cart-drawer .coupon-form {
                display: flex;
                margin-bottom: 10px;
            }

            .cart-drawer .coupon-input {
                flex: 1;
                padding: 10px;
                border: 1px solid var(--border-color);
                border-radius: 4px 0 0 4px;
                font-size: 14px;
            }

            .cart-drawer .apply-coupon-button {
                background: var(--cart-bg);
                color: var(--cart-text);
                border: none;
                border-radius: 0 4px 4px 0;
                padding: 0 15px;
                cursor: pointer;
                font-weight: 500;
            }

            .cart-drawer .apply-coupon-button:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
            }

            .cart-drawer .coupon-message {
                font-size: 14px;
                margin-bottom: 10px;
                min-height: 20px;
            }

            .cart-drawer .coupon-message.success {
                color: var(--success-color);
            }

            .cart-drawer .coupon-message.error {
                color: var(--danger-color);
            }

            .cart-drawer .applied-coupons {
                margin-top: 10px;
            }

            .cart-drawer .applied-coupons h4 {
                margin: 0 0 10px;
                font-size: 14px;
                color: var(--text-color);
            }

            .cart-drawer .applied-coupon {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px;
                background: white;
                border-radius: 4px;
                margin-bottom: 5px;
            }

            .cart-drawer .remove-coupon {
                background: none;
                border: none;
                color: var(--danger-color);
                cursor: pointer;
                font-size: 12px;
            }

            .cart-drawer .floating-cart-shipping-options {
                margin: auto 0 16px;
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
            }

            .cart-drawer .floating-cart-shipping-options h4 {
                margin: 0 0 10px;
                color: var(--text-color);
                font-size: 15px;
                font-weight: 700;
            }

            .cart-drawer .floating-cart-shipping-methods {
                display: grid;
                gap: 8px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .cart-drawer .floating-cart-shipping-methods label {
                display: flex;
                align-items: center;
                gap: 8px;
                color: var(--text-color);
                font-size: 13px;
            }

            .cart-drawer .cart-summary {
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
                margin-top: auto;
            }

            .cart-drawer .floating-cart-shipping-options + .cart-summary {
                margin-top: 0;
            }

            .cart-drawer .cart-summary--collapsible {
                padding: 0;
                overflow: visible;
                position: relative;
                z-index: 5;
            }

            .cart-drawer .cart-summary-toggle {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 10px;
                width: 100%;
                padding: 15px;
                border: 0;
                background: transparent;
                color: var(--text-color);
                cursor: pointer;
                font: inherit;
                text-align: left;
            }

            .cart-drawer .cart-summary-toggle:focus {
                outline: 2px solid var(--primary-color);
                outline-offset: -2px;
            }

            .cart-drawer .cart-summary-toggle__amount {
                font-weight: 600;
                margin-left: auto;
            }

            .cart-drawer .cart-summary-toggle__icon {
                width: 8px;
                height: 8px;
                border-right: 2px solid currentColor;
                border-bottom: 2px solid currentColor;
                transform: rotate(-135deg);
                transition: transform 0.2s ease;
            }

            .cart-drawer .cart-summary.is-collapsed .cart-summary-toggle__icon {
                transform: rotate(45deg);
            }

            .cart-drawer .cart-summary--collapsible .cart-summary__content {
                max-height: 360px;
                overflow: hidden;
                padding: 15px 15px;
                opacity: 1;
                transform: translateY(0);
                transition: max-height 0.28s ease, padding 0.28s ease, opacity 0.2s ease, transform 0.28s ease;
            }

            .cart-drawer .cart-summary--dropup .cart-summary__content {
                position: absolute;
                right: 0;
                bottom: 100%;
                left: 0;
                margin-bottom: 8px;
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
                box-shadow: 0 -10px 24px rgba(15, 23, 42, 0.12);
            }

            .cart-drawer .cart-summary.is-collapsed .cart-summary__content {
                max-height: 0;
                padding-top: 0;
                padding-bottom: 0;
                opacity: 0;
                pointer-events: none;
                transform: translateY(14px);
            }

            .cart-drawer .cart-summary--dropup.is-collapsed .cart-summary__content {
                margin-bottom: 0;
            }

            .cart-drawer .summary-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
            }

            .cart-drawer .summary-row:last-child {
                margin-bottom: 0;
            }

            .cart-drawer .summary-row.discount {
                color: var(--success-color);
            }

            .cart-drawer .summary-row.total {
                font-weight: 600;
                font-size: 16px;
                padding-top: 10px;
                border-top: 1px solid var(--border-color);
            }

            .cart-drawer .checkout-button {
                width: 100%;
                padding: 12px;
                background: var(--cart-bg);
                color: var(--cart-text);
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.2s;
                text-decoration: none;
            }

            .cart-drawer .checkout-button:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
            }

            .cart-drawer .you-may-also-like {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid var(--border-color);
            }

            .cart-drawer .you-may-also-like h3 {
                margin: 0 0 15px;
                font-size: 18px;
                color: var(--text-color);
            }

            .cart-drawer .recommended-products {
                display: flex;
                gap: 15px;
                flex-direction: row;
                flex-wrap: nowrap;
                width: 100%;
                overflow: auto;
                padding-bottom: 10px;
                scrollbar-width: thin;
            }

            .cart-drawer .recommended-products a {
                text-decoration: none;
            }

            .cart-drawer .recommended-product {
                text-align: center;
                max-width: 200px;
                min-width: 30%;
            }

            .cart-drawer .recommended-product img {
                width: 100%;
                height: 100px;
                object-fit: cover;
                border-radius: 6px;
                margin-bottom: 8px;
            }

            .cart-drawer .recommended-product h4 {
                margin: 0 0 5px;
                font-size: 14px;
                color: var(--text-color);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .cart-drawer .recommended-product .price {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                color: var(--cart-bg);
            }

            .cart-drawer .add-to-cart-button {
                width: 100%;
                padding: 6px;
                background: var(--cart-bg);
                color: var(--cart-text);
                border: none;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }

            .cart-drawer .add-to-cart-button:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
            }

            .cart-drawer .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {

                .plugincy_pos_,
                .plugincy_pos_fixed {
                    top: var(--cart-top);
                    left: var(--cart-left);
                    transform: translate(-100%);
                    <?php if ($border_radius == '50'): ?>border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    padding: 0;
                    <?php else: ?>border-radius: 50px;
                    padding: 12px 20px;
                    <?php endif; ?>
                }

                .plugincy_pos_:hover,
                .plugincy_pos_fixed:hover {
                    transform: translateX(-50%) translateY(-2px);
                }

                <?php if ($border_radius == '50'): ?>.cart-icon {
                    margin-right: 0;
                }

                span.cart-count {
                    top: -8px;
                    right: -8px;
                    left: auto;
                }

                <?php endif; ?>.cart-drawer {
                    max-width: 100%;
                }

                .cart-drawer .recommended-products {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
<?php endif;
}
