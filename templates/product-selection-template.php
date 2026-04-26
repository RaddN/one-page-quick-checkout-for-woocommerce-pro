<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$product_ids = array_values(array_filter(array_map('absint', isset($product_ids) ? $product_ids : array())));

if (empty($product_ids) || !function_exists('wc_get_product')) {
    return;
}

$atts = isset($atts) && is_array($atts) ? $atts : array();
$show_images = !empty($atts['show_images']) && in_array(strtolower((string) $atts['show_images']), array('1', 'yes', 'true', 'on'), true);
$product_layout = !empty($atts['product_layout']) ? sanitize_key($atts['product_layout']) : 'select_dropdown';
$allowed_product_layouts = array('select_dropdown', 'card_dropdown', 'cards');

if (!$show_images || !in_array($product_layout, $allowed_product_layouts, true)) {
    $product_layout = 'select_dropdown';
}

$sanitize_dimension = function ($value, $fallback, $min, $max) {
    if ($value === '' || $value === null || is_array($value)) {
        return $fallback;
    }

    $value = is_numeric($value) ? (float) $value : $fallback;
    $value = max((float) $min, min((float) $max, $value));

    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
};

$hex_to_rgba = function ($hex, $alpha) {
    $hex = sanitize_hex_color($hex);

    if (!$hex) {
        return 'rgba(143, 35, 60, ' . (float) $alpha . ')';
    }

    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $rgb = sscanf($hex, '%02x%02x%02x');

    if (!is_array($rgb) || count($rgb) < 3) {
        return 'rgba(143, 35, 60, ' . (float) $alpha . ')';
    }

    return sprintf('rgba(%d, %d, %d, %.2F)', $rgb[0], $rgb[1], $rgb[2], (float) $alpha);
};

$primary_color = !empty($atts['primary_color']) ? sanitize_hex_color($atts['primary_color']) : '';
$primary_color = $primary_color ? $primary_color : '#8f233c';
$secondary_color = !empty($atts['secondary_color']) ? sanitize_hex_color($atts['secondary_color']) : '';
$secondary_color = $secondary_color ? $secondary_color : '#6f6b64';
$border_radius = $sanitize_dimension(isset($atts['border_radius']) ? $atts['border_radius'] : '', 4, 0, 50);
$spacing = $sanitize_dimension(isset($atts['spacing']) ? $atts['spacing'] : '', 10, 0, 60);
$button_style = !empty($atts['button_style']) ? sanitize_key($atts['button_style']) : 'filled';
$allowed_button_styles = array('filled', 'outlined', 'text');

if (!in_array($button_style, $allowed_button_styles, true)) {
    $button_style = 'filled';
}

$product_selection_style = implode('; ', array(
    '--onepaqucpro-opc-primary: ' . $primary_color,
    '--onepaqucpro-opc-secondary: ' . $secondary_color,
    '--onepaqucpro-opc-muted: #6f6b64',
    '--onepaqucpro-opc-primary-soft: ' . $hex_to_rgba($primary_color, 0.08),
    '--onepaqucpro-opc-primary-subtle: ' . $hex_to_rgba($primary_color, 0.12),
    '--onepaqucpro-opc-primary-shadow: ' . $hex_to_rgba($primary_color, 0.10),
    '--onepaqucpro-opc-radius: ' . $border_radius . 'px',
    '--onepaqucpro-opc-spacing: ' . $spacing . 'px',
));

$current_product_id = 0;
$current_variation_id = 0;

if (function_exists('WC') && WC()->cart) {
    foreach (WC()->cart->get_cart() as $cart_item) {
        $cart_product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;

        if (in_array($cart_product_id, $product_ids, true)) {
            $current_product_id = $cart_product_id;
            $current_variation_id = !empty($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
            break;
        }
    }
}

$format_variation_attributes = function ($variation_attributes, $product = null) {
    $parts = array();

    foreach ((array) $variation_attributes as $attribute_key => $attribute_value) {
        if ($attribute_value === '') {
            continue;
        }

        $label = function_exists('onepaqucpro_get_variation_attribute_taxonomy_label')
            ? onepaqucpro_get_variation_attribute_taxonomy_label($attribute_key, $product)
            : wc_attribute_label(str_replace('attribute_', '', $attribute_key), $product);
        $value = function_exists('onepaqucpro_get_variation_attribute_label')
            ? onepaqucpro_get_variation_attribute_label($attribute_key, $attribute_value, $product)
            : $attribute_value;

        $parts[] = array(
            'label' => $label,
            'value' => $value,
        );
    }

    if (empty($parts)) {
        $parts[] = array(
            'label' => '',
            'value' => esc_html__('Default option', 'one-page-quick-checkout-for-woocommerce-pro'),
        );
    }

    return $parts;
};

$normalize_variation_attributes = function ($variation_attributes) {
    $normalized = array();

    foreach ((array) $variation_attributes as $attribute_key => $attribute_value) {
        if ($attribute_value === '' || $attribute_value === null || is_array($attribute_value)) {
            continue;
        }

        $attribute_key = function_exists('onepaqucpro_clean_variation_attribute_key')
            ? onepaqucpro_clean_variation_attribute_key($attribute_key)
            : preg_replace('/[^\p{L}\p{N}_\-%]/u', '', trim((string) $attribute_key));

        if ($attribute_key === '') {
            continue;
        }

        if (strpos($attribute_key, 'attribute_') !== 0) {
            $attribute_key = 'attribute_' . $attribute_key;
        }

        $attribute_value = function_exists('onepaqucpro_prepare_variation_attribute_value')
            ? onepaqucpro_prepare_variation_attribute_value($attribute_value)
            : preg_replace('/[\r\n\t\0\x0B]+/', ' ', wp_strip_all_tags(trim((string) $attribute_value)));

        if ($attribute_value === '') {
            continue;
        }

        $normalized[$attribute_key] = $attribute_value;
    }

    return $normalized;
};

$clean_price_text = function ($price_html) {
    $charset = function_exists('get_bloginfo') ? get_bloginfo('charset') : 'UTF-8';
    $price_text = html_entity_decode(wp_strip_all_tags((string) $price_html), ENT_QUOTES, $charset ? $charset : 'UTF-8');
    $price_text = str_replace("\xc2\xa0", ' ', $price_text);
    $price_text = preg_replace('/\s+/', ' ', $price_text);

    return trim($price_text);
};

$format_price_text = function ($product) use ($clean_price_text) {
    if (!$product || !is_object($product)) {
        return '';
    }

    if (method_exists($product, 'is_type') && $product->is_type('variable') && method_exists($product, 'get_variation_price')) {
        $min_price = $product->get_variation_price('min', true);
        $max_price = $product->get_variation_price('max', true);

        if ($min_price !== '' && $max_price !== '') {
            if ((float) $min_price === (float) $max_price) {
                return $clean_price_text(wc_price($min_price));
            }

            return $clean_price_text(wc_price($min_price) . ' - ' . wc_price($max_price));
        }
    }

    if (method_exists($product, 'get_price')) {
        $price = $product->get_price();
        if ($price !== '') {
            return $clean_price_text(wc_price($price));
        }
    }

    return method_exists($product, 'get_price_html') ? $clean_price_text($product->get_price_html()) : '';
};

$get_product_image_data = function ($product, $fallback_product = null) {
    $image_id = ($product && is_object($product) && method_exists($product, 'get_image_id')) ? absint($product->get_image_id()) : 0;

    if (!$image_id && $fallback_product && is_object($fallback_product) && method_exists($fallback_product, 'get_image_id')) {
        $image_id = absint($fallback_product->get_image_id());
    }

    $src = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail');
    $alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';

    if (!$alt && $product && is_object($product) && method_exists($product, 'get_name')) {
        $alt = $product->get_name();
    }

    return array(
        'src' => $src ? esc_url_raw($src) : '',
        'srcset' => $image_id ? wp_get_attachment_image_srcset($image_id, 'woocommerce_thumbnail') : '',
        'sizes' => $image_id ? wp_get_attachment_image_sizes($image_id, 'woocommerce_thumbnail') : '',
        'alt' => $alt ? wp_strip_all_tags($alt) : '',
    );
};

$products = array();

foreach ($product_ids as $product_id) {
    $product = wc_get_product($product_id);

    if (!$product || !$product->is_purchasable()) {
        continue;
    }

    if (!$current_product_id) {
        $current_product_id = $product_id;
    }

    $product_data = array(
        'id' => $product_id,
        'name' => $product->get_name(),
        'price' => $format_price_text($product),
        'type' => $product->get_type(),
        'variations' => array(),
        'show_variation_prices' => false,
    );

    if ($show_images) {
        $product_data['image'] = $get_product_image_data($product);
    }

    if ($product->is_type('variable')) {
        $available_variations = function_exists('onepaqucpro_get_validated_variations')
            ? onepaqucpro_get_validated_variations($product)
            : $product->get_available_variations();
        $variation_prices = array();

        foreach ((array) $available_variations as $variation_data) {
            $variation_id = !empty($variation_data['variation_id']) ? absint($variation_data['variation_id']) : 0;
            $variation = $variation_id ? wc_get_product($variation_id) : false;

            if (!$variation || !$variation->is_purchasable()) {
                continue;
            }

            $variation_attributes = !empty($variation_data['attributes']) && is_array($variation_data['attributes'])
                ? $normalize_variation_attributes($variation_data['attributes'])
                : $normalize_variation_attributes($variation->get_variation_attributes());
            $display_price = wc_get_price_to_display($variation);
            $variation_prices[] = wc_format_decimal($display_price, wc_get_price_decimals());

            $product_data['variations'][] = array(
                'id' => $variation_id,
                'parts' => $format_variation_attributes($variation_attributes, $product),
                'price' => $format_price_text($variation),
                'image' => $show_images ? $get_product_image_data($variation, $product) : array(),
                'attributes' => $variation_attributes,
            );
        }

        $product_data['show_variation_prices'] = count(array_unique($variation_prices)) > 1;
    }

    $products[] = $product_data;
}

if (empty($products)) {
    return;
}

$product_label = !empty($atts['product_label'])
    ? sanitize_text_field($atts['product_label'])
    : esc_html__('Product', 'one-page-quick-checkout-for-woocommerce-pro');
$variation_label = !empty($atts['variation_label'])
    ? sanitize_text_field($atts['variation_label'])
    : esc_html__('Choose an option', 'one-page-quick-checkout-for-woocommerce-pro');
$updating_selection_text = !empty($atts['updating_selection_text'])
    ? sanitize_text_field($atts['updating_selection_text'])
    : esc_html__('Updating selection...', 'one-page-quick-checkout-for-woocommerce-pro');
$current_product_data = !empty($products[0]) ? $products[0] : array();
$current_product_image = array();

if ($show_images) {
    foreach ($products as $product_data) {
        if ((int) $product_data['id'] === (int) $current_product_id) {
            $current_product_data = $product_data;
            $current_product_image = !empty($product_data['image']) ? $product_data['image'] : array();
            break;
        }
    }

    if (empty($current_product_image) && !empty($products[0]['image'])) {
        $current_product_image = $products[0]['image'];
    }
}

$field_id = function_exists('wp_unique_id') ? wp_unique_id('onepaqucpro-product-selection-') : 'onepaqucpro-product-selection-' . wp_rand(1000, 9999);
$variation_name = $field_id . '-variation';
$dropdown_id = $field_id . '-dropdown';
$dropdown_toggle_id = $field_id . '-dropdown-toggle';
$product_control_label_for = ($show_images && $product_layout !== 'cards') ? $dropdown_toggle_id : $field_id;
?>

<div class="onepaqucpro-product-selection button-style-<?php echo esc_attr($button_style); ?>" style="<?php echo esc_attr($product_selection_style); ?>" data-current-product="<?php echo esc_attr($current_product_id); ?>" data-current-variation="<?php echo esc_attr($current_variation_id); ?>" data-product-layout="<?php echo esc_attr($product_layout); ?>">
    <div class="onepaqucpro-product-selection__field">
        <label for="<?php echo esc_attr($product_control_label_for); ?>"><?php echo esc_html($product_label); ?></label>
        <div class="onepaqucpro-product-selection__product-control<?php echo $show_images ? ' has-image layout-' . esc_attr($product_layout) : ''; ?>">
            <?php if ($show_images && $product_layout !== 'cards') : ?>
                <div class="onepaqucpro-product-selection__dropdown is-<?php echo esc_attr($product_layout); ?>" data-dropdown-id="<?php echo esc_attr($dropdown_id); ?>">
                    <button
                        id="<?php echo esc_attr($dropdown_toggle_id); ?>"
                        type="button"
                        class="onepaqucpro-product-selection__dropdown-toggle"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($dropdown_id); ?>"
                    >
                        <span class="onepaqucpro-product-selection__dropdown-image">
                            <img
                                src="<?php echo esc_url(!empty($current_product_image['src']) ? $current_product_image['src'] : wc_placeholder_img_src('woocommerce_thumbnail')); ?>"
                                <?php if (!empty($current_product_image['srcset'])) : ?>srcset="<?php echo esc_attr($current_product_image['srcset']); ?>"<?php endif; ?>
                                <?php if (!empty($current_product_image['sizes'])) : ?>sizes="<?php echo esc_attr($current_product_image['sizes']); ?>"<?php endif; ?>
                                alt="<?php echo esc_attr(!empty($current_product_image['alt']) ? $current_product_image['alt'] : $product_label); ?>"
                                loading="lazy"
                            />
                        </span>
                        <span class="onepaqucpro-product-selection__dropdown-copy">
                            <span class="onepaqucpro-product-selection__dropdown-name"><?php echo esc_html(!empty($current_product_data['name']) ? $current_product_data['name'] : ''); ?></span>
                            <?php if (!empty($current_product_data['price'])) : ?>
                                <span class="onepaqucpro-product-selection__dropdown-price"><?php echo esc_html($current_product_data['price']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="onepaqucpro-product-selection__dropdown-caret" aria-hidden="true"></span>
                    </button>
                    <div id="<?php echo esc_attr($dropdown_id); ?>" class="onepaqucpro-product-selection__dropdown-menu" role="listbox">
                        <?php foreach ($products as $product_data) : ?>
                            <?php
                            $option_image = !empty($product_data['image']) ? $product_data['image'] : array();
                            $is_selected = (int) $current_product_id === (int) $product_data['id'];
                            ?>
                            <button
                                type="button"
                                class="onepaqucpro-product-selection__dropdown-option<?php echo $is_selected ? ' is-selected' : ''; ?>"
                                data-product-id="<?php echo esc_attr($product_data['id']); ?>"
                                role="option"
                                aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                            >
                                <span class="onepaqucpro-product-selection__dropdown-option-image">
                                    <img
                                        src="<?php echo esc_url(!empty($option_image['src']) ? $option_image['src'] : wc_placeholder_img_src('woocommerce_thumbnail')); ?>"
                                        <?php if (!empty($option_image['srcset'])) : ?>srcset="<?php echo esc_attr($option_image['srcset']); ?>"<?php endif; ?>
                                        <?php if (!empty($option_image['sizes'])) : ?>sizes="<?php echo esc_attr($option_image['sizes']); ?>"<?php endif; ?>
                                        alt="<?php echo esc_attr(!empty($option_image['alt']) ? $option_image['alt'] : $product_data['name']); ?>"
                                        loading="lazy"
                                    />
                                </span>
                                <span class="onepaqucpro-product-selection__dropdown-option-copy">
                                    <span class="onepaqucpro-product-selection__dropdown-option-name"><?php echo esc_html($product_data['name']); ?></span>
                                    <?php if (!empty($product_data['price'])) : ?>
                                        <span class="onepaqucpro-product-selection__dropdown-option-price"><?php echo esc_html($product_data['price']); ?></span>
                                    <?php endif; ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($show_images && $product_layout === 'cards') : ?>
                <div class="onepaqucpro-product-selection__product-cards" role="listbox" aria-label="<?php echo esc_attr($product_label); ?>">
                    <?php foreach ($products as $product_data) : ?>
                        <?php
                        $card_image = !empty($product_data['image']) ? $product_data['image'] : array();
                        $is_selected = (int) $current_product_id === (int) $product_data['id'];
                        ?>
                        <button
                            type="button"
                            class="onepaqucpro-product-selection__product-card<?php echo $is_selected ? ' is-selected' : ''; ?>"
                            data-product-id="<?php echo esc_attr($product_data['id']); ?>"
                            role="option"
                            aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                        >
                            <span class="onepaqucpro-product-selection__product-card-image">
                                <img
                                    src="<?php echo esc_url(!empty($card_image['src']) ? $card_image['src'] : wc_placeholder_img_src('woocommerce_thumbnail')); ?>"
                                    <?php if (!empty($card_image['srcset'])) : ?>srcset="<?php echo esc_attr($card_image['srcset']); ?>"<?php endif; ?>
                                    <?php if (!empty($card_image['sizes'])) : ?>sizes="<?php echo esc_attr($card_image['sizes']); ?>"<?php endif; ?>
                                    alt="<?php echo esc_attr(!empty($card_image['alt']) ? $card_image['alt'] : $product_data['name']); ?>"
                                    loading="lazy"
                                />
                            </span>
                            <span class="onepaqucpro-product-selection__product-card-copy">
                                <span class="onepaqucpro-product-selection__product-card-name"><?php echo esc_html($product_data['name']); ?></span>
                                <?php if (!empty($product_data['price'])) : ?>
                                    <span class="onepaqucpro-product-selection__product-card-price"><?php echo esc_html($product_data['price']); ?></span>
                                <?php endif; ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <select id="<?php echo esc_attr($field_id); ?>" class="onepaqucpro-product-selection__product<?php echo $show_images ? ' is-enhanced' : ''; ?>" <?php echo $show_images ? 'aria-hidden="true" tabindex="-1"' : ''; ?>>
                <?php foreach ($products as $product_data) : ?>
                    <option value="<?php echo esc_attr($product_data['id']); ?>" <?php selected($current_product_id, $product_data['id']); ?>>
                        <?php
                        echo esc_html(
                            trim($product_data['name'] . (!empty($product_data['price']) ? ' - ' . $product_data['price'] : ''))
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="onepaqucpro-product-selection__field onepaqucpro-product-selection__variations-wrap">
        <span class="onepaqucpro-product-selection__label"><?php echo esc_html($variation_label); ?></span>
        <div class="onepaqucpro-product-selection__variations" role="radiogroup"></div>
    </div>

    <p class="onepaqucpro-product-selection__status" aria-live="polite"></p>
</div>

<style>
    .onepaqucpro-product-selection {
        margin: 0 0 calc(var(--onepaqucpro-opc-spacing) * 1.8);
    }

    .onepaqucpro-product-selection__field {
        margin-bottom: calc(var(--onepaqucpro-opc-spacing) * 1.4);
    }

    .onepaqucpro-product-selection__field > label,
    .onepaqucpro-product-selection__label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
    }

    .onepaqucpro-product-selection select {
        width: 100%;
        min-height: 46px;
        border-radius: var(--onepaqucpro-opc-radius);
    }

    .onepaqucpro-product-selection select:focus {
        border-color: var(--onepaqucpro-opc-primary);
        box-shadow: 0 0 0 2px var(--onepaqucpro-opc-primary-subtle);
        outline: none;
    }

    .onepaqucpro-product-selection__product-control {
        position: relative;
    }

    .onepaqucpro-product-selection__product.is-enhanced {
        display: none;
    }

    .onepaqucpro-product-selection__dropdown {
        position: relative;
        width: 100%;
    }

    .onepaqucpro-product-selection__dropdown-toggle {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) 18px;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 66px;
        padding: 8px 12px;
        border: 1px solid #d6dbe3;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff !important;
        color: #111827 !important;
        font: inherit;
        line-height: 1.3;
        text-align: left;
        cursor: pointer;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .onepaqucpro-product-selection__dropdown-toggle:hover,
    .onepaqucpro-product-selection__dropdown-toggle:focus {
        border-color: var(--onepaqucpro-opc-primary);
        box-shadow: 0 0 0 2px var(--onepaqucpro-opc-primary-subtle);
        outline: none;
    }

    .onepaqucpro-product-selection__dropdown.is-open .onepaqucpro-product-selection__dropdown-toggle {
        border-color: var(--onepaqucpro-opc-primary);
    }

    .onepaqucpro-product-selection__dropdown-image,
    .onepaqucpro-product-selection__dropdown-option-image,
    .onepaqucpro-product-selection__variation-image {
        display: block;
        overflow: hidden;
        border: 1px solid #e5e0d7;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff;
        flex: 0 0 auto;
    }

    .onepaqucpro-product-selection__dropdown-image,
    .onepaqucpro-product-selection__dropdown-option-image {
        width: 50px;
        height: 50px;
    }

    .onepaqucpro-product-selection__variation-image {
        width: 44px;
        height: 44px;
    }

    .onepaqucpro-product-selection__dropdown-image img,
    .onepaqucpro-product-selection__dropdown-option-image img,
    .onepaqucpro-product-selection__variation-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .onepaqucpro-product-selection__dropdown-copy,
    .onepaqucpro-product-selection__dropdown-option-copy {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .onepaqucpro-product-selection__dropdown-name,
    .onepaqucpro-product-selection__dropdown-option-name {
        color: #111827;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .onepaqucpro-product-selection__dropdown-price,
    .onepaqucpro-product-selection__dropdown-option-price {
        color: var(--onepaqucpro-opc-muted);
        font-size: 13px;
        font-weight: 500;
    }

    .onepaqucpro-product-selection__dropdown-caret {
        position: relative;
        width: 12px;
        height: 12px;
        justify-self: center;
    }

    .onepaqucpro-product-selection__dropdown-caret::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 8px;
        height: 8px;
        border-right: 2px solid #4b5563;
        border-bottom: 2px solid #4b5563;
        transform: rotate(45deg);
        transition: transform 160ms ease;
    }

    .onepaqucpro-product-selection__dropdown.is-open .onepaqucpro-product-selection__dropdown-caret::after {
        top: 5px;
        transform: rotate(225deg);
    }

    .onepaqucpro-product-selection__dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 999;
        display: none;
        max-height: 320px;
        overflow-y: auto;
        padding: 6px;
        border: 1px solid #d6dbe3;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff;
        box-shadow: 0 14px 34px rgba(17, 24, 39, 0.14);
    }

    .onepaqucpro-product-selection__dropdown.is-open .onepaqucpro-product-selection__dropdown-menu {
        display: grid;
        gap: calc(var(--onepaqucpro-opc-spacing) * 0.4);
    }

    .onepaqucpro-product-selection__dropdown-option {
        display: grid;
        grid-template-columns: 50px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 8px;
        border: 1px solid transparent;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff !important;
        color: #111827 !important;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    .onepaqucpro-product-selection__dropdown-option:hover,
    .onepaqucpro-product-selection__dropdown-option:focus {
        border-color: #e5e0d7;
        background: #fbfaf8 !important;
        outline: none;
    }

    .onepaqucpro-product-selection__dropdown-option.is-selected {
        border-color: var(--onepaqucpro-opc-primary);
        background: var(--onepaqucpro-opc-primary-soft) !important;
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-menu {
        grid-template-columns: repeat(auto-fill, minmax(min(180px, 100%), 220px));
        align-items: stretch;
        justify-content: start;
        gap: var(--onepaqucpro-opc-spacing);
        padding: var(--onepaqucpro-opc-spacing);
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option {
        position: relative;
        grid-template-columns: 1fr;
        align-content: start;
        gap: 9px;
        min-height: 0;
        padding: var(--onepaqucpro-opc-spacing);
        border-color: #e5e0d7;
        background: #fff !important;
        box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option:hover,
    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option:focus {
        border-color: #c9bfb0;
        background: #fff !important;
        box-shadow: 0 8px 18px rgba(17, 24, 39, 0.08);
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option.is-selected {
        border-color: var(--onepaqucpro-opc-primary);
        background: var(--onepaqucpro-opc-primary-soft) !important;
        box-shadow: inset 3px 0 0 var(--onepaqucpro-opc-primary), 0 8px 18px var(--onepaqucpro-opc-primary-shadow);
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option.is-selected::after {
        content: "";
        position: absolute;
        top: 12px;
        right: 12px;
        width: 10px;
        height: 6px;
        border-left: 2px solid var(--onepaqucpro-opc-primary);
        border-bottom: 2px solid var(--onepaqucpro-opc-primary);
        transform: rotate(-45deg);
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option-image {
        width: 100%;
        height: auto;
        max-height: 132px;
        aspect-ratio: 16 / 10;
        border-radius: var(--onepaqucpro-opc-radius);
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option-copy {
        gap: 5px;
    }

    .onepaqucpro-product-selection__dropdown.is-card_dropdown .onepaqucpro-product-selection__dropdown-option-name {
        display: -webkit-box;
        overflow: hidden;
        white-space: normal;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .onepaqucpro-product-selection__product-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: var(--onepaqucpro-opc-spacing);
    }

    .onepaqucpro-product-selection__product-card {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 86px;
        padding: var(--onepaqucpro-opc-spacing);
        border: 1px solid #e5e0d7;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff !important;
        color: #111827 !important;
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .onepaqucpro-product-selection__product-card:hover,
    .onepaqucpro-product-selection__product-card:focus {
        border-color: var(--onepaqucpro-opc-primary);
        box-shadow: 0 0 0 2px var(--onepaqucpro-opc-primary-subtle);
        outline: none;
    }

    .onepaqucpro-product-selection__product-card.is-selected {
        border-color: var(--onepaqucpro-opc-primary);
        background: var(--onepaqucpro-opc-primary-soft) !important;
    }

    .onepaqucpro-product-selection__product-card-image {
        display: block;
        width: 64px;
        height: 64px;
        overflow: hidden;
        border: 1px solid #e5e0d7;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff;
    }

    .onepaqucpro-product-selection__product-card-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .onepaqucpro-product-selection__product-card-copy {
        display: grid;
        gap: 4px;
        min-width: 0;
    }

    .onepaqucpro-product-selection__product-card-name {
        color: #111827;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .onepaqucpro-product-selection__product-card-price {
        color: var(--onepaqucpro-opc-muted);
        font-size: 13px;
        font-weight: 500;
    }

    .onepaqucpro-product-selection__variations {
        display: grid;
        gap: calc(var(--onepaqucpro-opc-spacing) * 0.8);
    }

    .onepaqucpro-product-selection__variation {
        display: grid;
        grid-template-columns: 18px minmax(0, 1fr);
        align-items: flex-start;
        gap: 12px;
        margin: 0;
        padding: 12px 14px;
        border: 1px solid #e5e0d7;
        border-radius: var(--onepaqucpro-opc-radius);
        background: #fff;
        line-height: 1.4;
        cursor: pointer;
        transition: border-color 160ms ease, background-color 160ms ease;
    }

    .onepaqucpro-product-selection__variation.has-image {
        grid-template-columns: 18px 44px minmax(0, 1fr);
        align-items: center;
    }

    .onepaqucpro-product-selection__variation:hover {
        border-color: #c9bfb0;
    }

    .onepaqucpro-product-selection__variation.is-selected {
        border-color: var(--onepaqucpro-opc-primary);
        background: var(--onepaqucpro-opc-primary-soft);
    }

    .onepaqucpro-product-selection__variation input {
        width: 16px;
        height: 16px;
        margin: 2px 0 0;
        accent-color: var(--onepaqucpro-opc-primary);
    }

    .onepaqucpro-product-selection__variation-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-width: 0;
    }

    .onepaqucpro-product-selection__variation-attributes {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        min-width: 0;
    }

    .onepaqucpro-product-selection__variation-name {
        font-weight: 600;
        color: #111827;
        overflow-wrap: anywhere;
    }

    .onepaqucpro-product-selection__variation-attribute {
        color: var(--onepaqucpro-opc-muted);
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .onepaqucpro-product-selection__variation-attribute-label {
        margin-right: 4px;
    }

    .onepaqucpro-product-selection__variation-price {
        margin-left: auto;
        color: var(--onepaqucpro-opc-muted);
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
    }

    .onepaqucpro-product-selection__status {
        min-height: 18px;
        margin: 0;
        color: var(--onepaqucpro-opc-muted);
        font-size: 13px;
    }

    .onepaqucpro-product-selection.is-loading {
        opacity: 0.68;
        pointer-events: none;
    }

    @media (max-width: 600px) {
        .onepaqucpro-product-selection__dropdown-toggle {
            grid-template-columns: 48px minmax(0, 1fr) 18px;
            min-height: 60px;
            padding: 7px 10px;
        }

        .onepaqucpro-product-selection__dropdown-image,
        .onepaqucpro-product-selection__dropdown-option-image {
            width: 48px;
            height: 48px;
        }

        .onepaqucpro-product-selection__dropdown-menu {
            max-height: 260px;
        }

        .onepaqucpro-product-selection__product-cards {
            grid-template-columns: 1fr;
        }

        .onepaqucpro-product-selection__variation-content {
            align-items: flex-start;
            flex-direction: column;
            gap: 3px;
        }

        .onepaqucpro-product-selection__variation-attributes {
            align-items: flex-start;
            flex-direction: column;
            gap: 3px;
        }

        .onepaqucpro-product-selection__variation-price {
            margin-left: 0;
            white-space: normal;
        }
    }
</style>

<?php
$inline_script = 'jQuery(function($) {
    var products = ' . wp_json_encode($products) . ';
    var $template = $(".onepaqucpro-product-selection").last();
    var $product = $template.find(".onepaqucpro-product-selection__product");
    var $variations = $template.find(".onepaqucpro-product-selection__variations");
    var $variationsWrap = $template.find(".onepaqucpro-product-selection__variations-wrap");
    var $status = $template.find(".onepaqucpro-product-selection__status");
    var $dropdown = $template.find(".onepaqucpro-product-selection__dropdown");
    var $dropdownToggle = $template.find(".onepaqucpro-product-selection__dropdown-toggle");
    var $dropdownMenu = $template.find(".onepaqucpro-product-selection__dropdown-menu");
    var $dropdownOptions = $template.find(".onepaqucpro-product-selection__dropdown-option");
    var $dropdownImage = $template.find(".onepaqucpro-product-selection__dropdown-image img");
    var $dropdownName = $template.find(".onepaqucpro-product-selection__dropdown-name");
    var $dropdownPrice = $template.find(".onepaqucpro-product-selection__dropdown-price");
    var $productCards = $template.find(".onepaqucpro-product-selection__product-card");
    var currentVariation = String($template.data("current-variation") || "");
    var radioName = ' . wp_json_encode($variation_name) . ';
    var showImages = ' . wp_json_encode($show_images) . ';
    var productLayout = ' . wp_json_encode($product_layout) . ';
    var updatingSelectionText = ' . wp_json_encode($updating_selection_text) . ';

    function findProduct(productId) {
        productId = parseInt(productId, 10);
        for (var i = 0; i < products.length; i++) {
            if (parseInt(products[i].id, 10) === productId) {
                return products[i];
            }
        }
        return null;
    }

    function applyImage($img, image) {
        if (!$img || !$img.length || !image || !image.src) {
            return;
        }

        $img.attr("src", image.src);
        $img.attr("alt", image.alt || "");

        if (image.srcset) {
            $img.attr("srcset", image.srcset);
        } else {
            $img.removeAttr("srcset");
        }

        if (image.sizes) {
            $img.attr("sizes", image.sizes);
        } else {
            $img.removeAttr("sizes");
        }
    }

    function closeDropdown() {
        if (!$dropdown.length) {
            return;
        }

        $dropdown.removeClass("is-open");
        $dropdownToggle.attr("aria-expanded", "false");
    }

    function openDropdown() {
        if (!$dropdown.length) {
            return;
        }

        $dropdown.addClass("is-open");
        $dropdownToggle.attr("aria-expanded", "true");
    }

    function updateProductControls(productData) {
        if (!showImages || !productData) {
            return;
        }

        if ($dropdown.length) {
            if (productData.image) {
                applyImage($dropdownImage, productData.image);
            }

            $dropdownName.text(productData.name || "");

            if (productData.price) {
                if (!$dropdownPrice.length) {
                    $dropdownPrice = $("<span/>", {
                        "class": "onepaqucpro-product-selection__dropdown-price"
                    }).appendTo($template.find(".onepaqucpro-product-selection__dropdown-copy"));
                }
                $dropdownPrice.text(productData.price).show();
            } else {
                $dropdownPrice.text("").hide();
            }

            $dropdownOptions
                .removeClass("is-selected")
                .attr("aria-selected", "false")
                .filter("[data-product-id=\"" + productData.id + "\"]")
                .addClass("is-selected")
                .attr("aria-selected", "true");
        }

        $productCards
            .removeClass("is-selected")
            .attr("aria-selected", "false")
            .filter("[data-product-id=\"" + productData.id + "\"]")
            .addClass("is-selected")
            .attr("aria-selected", "true");
    }

    function renderVariations() {
        var selectedProduct = findProduct($product.val());
        $variations.empty();
        updateProductControls(selectedProduct);

        if (!selectedProduct || !selectedProduct.variations || !selectedProduct.variations.length) {
            $variationsWrap.hide();
            return;
        }

        $variationsWrap.show();
        var hasCurrentVariation = false;

        if (currentVariation) {
            $.each(selectedProduct.variations, function(index, variation) {
                if (String(variation.id) === currentVariation) {
                    hasCurrentVariation = true;
                    return false;
                }
            });
        }

        $.each(selectedProduct.variations, function(index, variation) {
            var variationId = String(variation.id);
            var checked = hasCurrentVariation ? variationId === currentVariation : index === 0;
            var $label = $("<label/>", {
                "class": "onepaqucpro-product-selection__variation" + (showImages ? " has-image" : "") + (checked ? " is-selected" : "")
            });
            var $radio = $("<input/>", {
                type: "radio",
                name: radioName,
                value: variationId,
                checked: checked
            }).data("variation", variation);

            $label.append($radio);

            if (showImages && variation.image && variation.image.src) {
                var $image = $("<span/>", {
                    "class": "onepaqucpro-product-selection__variation-image"
                });
                var $img = $("<img/>", {
                    src: variation.image.src,
                    alt: variation.image.alt || "",
                    loading: "lazy"
                });

                if (variation.image.srcset) {
                    $img.attr("srcset", variation.image.srcset);
                }

                if (variation.image.sizes) {
                    $img.attr("sizes", variation.image.sizes);
                }

                $image.append($img);
                $label.append($image);
            }

            var $content = $("<span/>", {
                "class": "onepaqucpro-product-selection__variation-content"
            });
            var $attributes = $("<span/>", {
                "class": "onepaqucpro-product-selection__variation-attributes"
            });
            var parts = $.isArray(variation.parts) && variation.parts.length ? variation.parts : [{
                label: "",
                value: variation.label || ""
            }];

            $attributes.append($("<span/>", {
                "class": "onepaqucpro-product-selection__variation-name",
                text: parts[0].value || ""
            }));

            $.each(parts.slice(1), function(partIndex, part) {
                var $part = $("<span/>", {
                    "class": "onepaqucpro-product-selection__variation-attribute"
                });

                if (part.label) {
                    $part.append($("<span/>", {
                        "class": "onepaqucpro-product-selection__variation-attribute-label",
                        text: part.label
                    }));
                }

                $part.append($("<span/>", {
                    "class": "onepaqucpro-product-selection__variation-attribute-value",
                    text: part.value || ""
                }));
                $attributes.append($part);
            });

            $content.append($attributes);

            if (selectedProduct.show_variation_prices && variation.price) {
                $content.append($("<span/>", {
                    "class": "onepaqucpro-product-selection__variation-price",
                    text: variation.price
                }));
            }

            $label.append($content);
            $variations.append($label);
        });
    }

    function selectedVariation() {
        var $checked = $variations.find("input:checked");
        return $checked.length ? $checked.data("variation") : null;
    }

    function syncSelection() {
        var selectedProduct = findProduct($product.val());
        var variation = selectedVariation();

        if (!selectedProduct) {
            return;
        }

        $template.addClass("is-loading");
        $status.text(updatingSelectionText);

        $.ajax({
            type: "POST",
            url: (window.onepaqucpro_wc_cart_params && onepaqucpro_wc_cart_params.ajax_url) || (window.wc_add_to_cart_params && wc_add_to_cart_params.ajax_url),
            data: {
                action: "onepaqucpro_product_selection_select_product",
                product_id: selectedProduct.id,
                variation_id: variation ? variation.id : 0,
                variations: variation ? variation.attributes : {},
                nonce: window.onepaqucpro_wc_cart_params ? onepaqucpro_wc_cart_params.nonce : ""
            }
        }).done(function(response) {
            if (response && response.success) {
                $status.text("");
                $(document.body).trigger("update_checkout");
                return;
            }

            $status.text(response && response.data && response.data.message ? response.data.message : "' . esc_js(__('Could not update the selected product.', 'one-page-quick-checkout-for-woocommerce-pro')) . '");
        }).fail(function() {
            $status.text("' . esc_js(__('Could not update the selected product.', 'one-page-quick-checkout-for-woocommerce-pro')) . '");
        }).always(function() {
            $template.removeClass("is-loading");
        });
    }

    $product.on("change", function() {
        currentVariation = "";
        renderVariations();
        syncSelection();
    });

    if (showImages && $dropdown.length) {
        $dropdownToggle.on("click", function(e) {
            e.preventDefault();
            e.stopPropagation();

            if ($dropdown.hasClass("is-open")) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        $dropdownToggle.on("keydown", function(e) {
            if (e.key === "Enter" || e.key === " " || e.key === "ArrowDown") {
                e.preventDefault();
                openDropdown();
                $dropdownOptions.filter(".is-selected").first().focus();
            }
        });

        $dropdownMenu.on("click", ".onepaqucpro-product-selection__dropdown-option", function(e) {
            e.preventDefault();
            e.stopPropagation();

            var productId = String($(this).data("product-id") || "");
            if (!productId || String($product.val()) === productId) {
                closeDropdown();
                $dropdownToggle.focus();
                return;
            }

            $product.val(productId).trigger("change");
            closeDropdown();
            $dropdownToggle.focus();
        });

        $dropdownMenu.on("keydown", ".onepaqucpro-product-selection__dropdown-option", function(e) {
            var $options = $dropdownOptions;
            var currentIndex = $options.index(this);

            if (e.key === "Escape") {
                e.preventDefault();
                closeDropdown();
                $dropdownToggle.focus();
            } else if (e.key === "ArrowDown") {
                e.preventDefault();
                $options.eq(Math.min(currentIndex + 1, $options.length - 1)).focus();
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                $options.eq(Math.max(currentIndex - 1, 0)).focus();
            } else if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                $(this).trigger("click");
            }
        });

        $(document).on("click.onepaqucproProductSelection", function(e) {
            if ($template.length && !$template[0].contains(e.target)) {
                closeDropdown();
            }
        });
    }

    if (showImages && productLayout === "cards" && $productCards.length) {
        $template.on("click", ".onepaqucpro-product-selection__product-card", function(e) {
            e.preventDefault();

            var productId = String($(this).data("product-id") || "");
            if (!productId || String($product.val()) === productId) {
                return;
            }

            $product.val(productId).trigger("change");
        });

        $template.on("keydown", ".onepaqucpro-product-selection__product-card", function(e) {
            var $cards = $template.find(".onepaqucpro-product-selection__product-card");
            var currentIndex = $cards.index(this);

            if (e.key === "ArrowDown" || e.key === "ArrowRight") {
                e.preventDefault();
                $cards.eq(Math.min(currentIndex + 1, $cards.length - 1)).focus();
            } else if (e.key === "ArrowUp" || e.key === "ArrowLeft") {
                e.preventDefault();
                $cards.eq(Math.max(currentIndex - 1, 0)).focus();
            } else if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                $(this).trigger("click");
            }
        });
    }

    $variations.on("change", "input[type=radio]", function() {
        currentVariation = String($(this).val());
        $variations.find(".onepaqucpro-product-selection__variation").removeClass("is-selected");
        $(this).closest(".onepaqucpro-product-selection__variation").addClass("is-selected");
        syncSelection();
    });

    renderVariations();
});';

wp_add_inline_script('rmenupro-cart-script', $inline_script, 'after');
?>
