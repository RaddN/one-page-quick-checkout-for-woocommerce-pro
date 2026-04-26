<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the Plugincy One Page Checkout Gutenberg Block
 */
if (!function_exists('onepaquc_register_one_page_checkout_block')) {
function onepaqucpro_register_one_page_checkout_block()
{
    // Skip block registration if Gutenberg is not available
    if (!function_exists('register_block_type')) {
        return;
    }

     // Check if block type is already registered
    if (WP_Block_Type_Registry::get_instance()->is_registered('plugincy/one-page-checkout')) {
        return; // Exit early if already registered
    }

    // Register the block script
    wp_register_script(
        'plugincy-one-page-checkout-block',
        plugins_url('one-page-checkout-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'one-page-checkout-block.js'),
        true
    );

    wp_localize_script(
        'plugincy-one-page-checkout-block',
        'onepaqucproOnePageCheckoutBlock',
        array(
            'isLicenseActive' => function_exists('onepaqucpro_can_use_one_page_checkout_feature') ? onepaqucpro_can_use_one_page_checkout_feature() : false,
            'proTitle' => esc_html__('Pro version only.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'proMessage' => esc_html__('Multi Product One Page Checkout requires an active Pro license. Please activate your license to use this feature.', 'one-page-quick-checkout-for-woocommerce-pro'),
        )
    );

    // Register optional block editor styles
    wp_register_style(
        'plugincy-one-page-checkout-editor',
        plugins_url('one-page-checkout-editor.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'one-page-checkout-editor.css')
    );

    // Register the block
    register_block_type('plugincy/one-page-checkout', array(
        'editor_script' => 'plugincy-one-page-checkout-block',
        'editor_style'  => 'plugincy-one-page-checkout-editor',
        'render_callback' => 'onepaqucpro_render_one_page_checkout_block',
        'attributes' => array(
            'product_ids' => array(
                'type' => 'string',
                'default' => '',
            ),
            'category' => array(
                'type' => 'string',
                'default' => '',
            ),
            'tags' => array(
                'type' => 'string',
                'default' => '',
            ),
            'attribute' => array(
                'type' => 'string',
                'default' => '',
            ),
            'terms' => array(
                'type' => 'string',
                'default' => '',
            ),
            'template' => array(
                'type' => 'string',
                'default' => 'product-tabs',
            ),
            'position' => array(
                'type' => 'string',
                'default' => 'after_description',
            ),
            'product_label' => array(
                'type' => 'string',
                'default' => 'Product',
            ),
            'variation_label' => array(
                'type' => 'string',
                'default' => 'Choose an option',
            ),
            'updating_selection_text' => array(
                'type' => 'string',
                'default' => 'Updating selection...',
            ),
            'show_images' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'product_layout' => array(
                'type' => 'string',
                'default' => 'select_dropdown',
            ),
            // Style attributes
            'borderRadius' => array(
                'type' => 'number',
                'default' => 4,
            ),
            'boxShadow' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'primaryColor' => array(
                'type' => 'string',
                'default' => '#4CAF50',
            ),
            'secondaryColor' => array(
                'type' => 'string',
                'default' => '#2196F3',
            ),
            'buttonStyle' => array(
                'type' => 'string',
                'default' => 'filled',
            ),
            'spacing' => array(
                'type' => 'number',
                'default' => 15,
            ),
        ),
    ));
}
add_action('init', 'onepaqucpro_register_one_page_checkout_block',100);
}

/**
 * Render callback for the Plugincy One Page Checkout block
 *
 * @param array $attributes Block attributes.
 * @return string Generated shortcode.
 */
function onepaqucpro_render_one_page_checkout_block($attributes)
{
    if (function_exists('onepaqucpro_can_use_one_page_checkout_feature') && !onepaqucpro_can_use_one_page_checkout_feature()) {
        return function_exists('onepaqucpro_get_one_page_checkout_license_notice')
            ? onepaqucpro_get_one_page_checkout_license_notice()
            : '<div class="onepaqucpro-license-required">' . esc_html__('Pro version only. Please activate your license to use this feature.', 'one-page-quick-checkout-for-woocommerce-pro') . '</div>';
    }

    // Extract and sanitize attributes
    $product_ids = isset($attributes['product_ids']) ? sanitize_text_field($attributes['product_ids']) : '';
    $category = isset($attributes['category']) ? sanitize_text_field($attributes['category']) : '';
    $tags = isset($attributes['tags']) ? sanitize_text_field($attributes['tags']) : '';
    $attribute = isset($attributes['attribute']) ? sanitize_text_field($attributes['attribute']) : '';
    $terms = isset($attributes['terms']) ? sanitize_text_field($attributes['terms']) : '';
    $template = isset($attributes['template']) ? sanitize_text_field($attributes['template']) : 'product-tabs';
    $position = isset($attributes['position']) ? sanitize_key($attributes['position']) : 'after_description';
    $product_label = isset($attributes['product_label']) ? sanitize_text_field($attributes['product_label']) : '';
    $variation_label = isset($attributes['variation_label']) ? sanitize_text_field($attributes['variation_label']) : '';
    $updating_selection_text = isset($attributes['updating_selection_text']) ? sanitize_text_field($attributes['updating_selection_text']) : '';
    $show_images = !empty($attributes['show_images']);
    $product_layout = isset($attributes['product_layout']) ? sanitize_key($attributes['product_layout']) : 'select_dropdown';
    $borderRadius = isset($attributes['borderRadius']) ? intval($attributes['borderRadius']) : 4;
    $boxShadow = isset($attributes['boxShadow']) ? (bool) $attributes['boxShadow'] : false;
    $primaryColor = isset($attributes['primaryColor']) ? sanitize_hex_color($attributes['primaryColor']) : '#4CAF50';
    $primaryColor = $primaryColor ? $primaryColor : '#4CAF50';
    $secondaryColor = isset($attributes['secondaryColor']) ? sanitize_hex_color($attributes['secondaryColor']) : '#2196F3';
    $secondaryColor = $secondaryColor ? $secondaryColor : '#2196F3';
    $buttonStyle = isset($attributes['buttonStyle']) ? sanitize_text_field($attributes['buttonStyle']) : 'filled';
    $spacing = isset($attributes['spacing']) ? intval($attributes['spacing']) : 15;

    $custom_styles = '<style>
        .one-page-checkout-container {
            border-radius: ' . esc_attr($borderRadius) . 'px;
            box-shadow: ' . ($boxShadow ? '0 2px 10px rgba(0, 0, 0, 0.1)' : 'none') . ';
        }
        .one-page-checkout-container .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: ' . esc_attr($spacing) . 'px;
        }
        .one-page-checkout-container button#place_order {
            border-radius: ' . esc_attr($borderRadius) . 'px;
            padding: 10px 20px;
            margin-bottom: ' . esc_attr($spacing) . 'px;
            ' . (
            $buttonStyle === 'outlined' ? '
                background-color: transparent;
                color: ' . esc_attr($primaryColor) . ';
                border: 2px solid ' . esc_attr($primaryColor) . ';
            ' : (
                $buttonStyle === 'text' ? '
                background-color: transparent;
                color: ' . esc_attr($primaryColor) . ';
                border: none;
                ' : '
                background-color: ' . esc_attr($primaryColor) . ';
                color: ' . esc_attr($secondaryColor) . ';
                border: none;
                '
            )
            ) . '
        }
        </style>';

    // Build shortcode attributes array
    $shortcode_atts = array();

    if (!empty($product_ids)) {
        $shortcode_atts[] = 'product_ids="' . esc_attr($product_ids) . '"';
    }

    if (!empty($category)) {
        $shortcode_atts[] = 'category="' . esc_attr($category) . '"';
    }

    if (!empty($tags)) {
        $shortcode_atts[] = 'tags="' . esc_attr($tags) . '"';
    }

    if (!empty($attribute)) {
        $shortcode_atts[] = 'attribute="' . esc_attr($attribute) . '"';
    }

    if (!empty($terms)) {
        $shortcode_atts[] = 'terms="' . esc_attr($terms) . '"';
    }

    if (!empty($template)) {
        $shortcode_atts[] = 'template="' . esc_attr($template) . '"';
    }

    if ($template === 'product-selection' && !empty($position)) {
        $shortcode_atts[] = 'position="' . esc_attr($position) . '"';
    }

    if ($template === 'product-selection' && $product_label !== '') {
        $shortcode_atts[] = 'product_label="' . esc_attr($product_label) . '"';
    }

    if ($template === 'product-selection' && $variation_label !== '') {
        $shortcode_atts[] = 'variation_label="' . esc_attr($variation_label) . '"';
    }

    if ($template === 'product-selection' && $updating_selection_text !== '') {
        $shortcode_atts[] = 'updating_selection_text="' . esc_attr($updating_selection_text) . '"';
    }

    if ($template === 'product-selection' && $show_images) {
        $shortcode_atts[] = 'show_images="yes"';
    }

    if ($template === 'product-selection' && $show_images && !empty($product_layout)) {
        $shortcode_atts[] = 'product_layout="' . esc_attr($product_layout) . '"';
    }

    if ($template === 'product-selection') {
        if (!empty($primaryColor)) {
            $shortcode_atts[] = 'primary_color="' . esc_attr($primaryColor) . '"';
        }

        if (!empty($secondaryColor)) {
            $shortcode_atts[] = 'secondary_color="' . esc_attr($secondaryColor) . '"';
        }

        $shortcode_atts[] = 'border_radius="' . esc_attr($borderRadius) . '"';
        $shortcode_atts[] = 'spacing="' . esc_attr($spacing) . '"';

        if (!empty($buttonStyle)) {
            $shortcode_atts[] = 'button_style="' . esc_attr($buttonStyle) . '"';
        }
    }

    // Generate the shortcode
    $shortcode = '[plugincy_one_page_checkout';
    if (!empty($shortcode_atts)) {
        $shortcode .= ' ' . implode(' ', $shortcode_atts);
    }
    $shortcode .= ']';

    return $custom_styles . $shortcode;
}

/**
 * Add custom block category for Plugincy blocks
 */
function onepaqucpro_add_block_category($categories)
{
    // Check if the category already exists
    foreach ($categories as $category) {
        if ($category['slug'] === 'plugincy') {
            return $categories;
        }
    }

    // Add Plugincy category at the beginning
    return array_merge(
        array(
            array(
                'slug'  => 'plugincy',
                'title' => esc_html__('Plugincy', 'one-page-quick-checkout-for-woocommerce-pro'),
                'icon'  => 'cart',
            ),
        ),
       is_array($categories) ? $categories : []
    );
}
add_filter('block_categories_all', 'onepaqucpro_add_block_category');

/**
 * Enqueue block editor assets
 */
function onepaqucpro_enqueue_block_editor_assets()
{
    // Add custom styles to the block editor
    $custom_css = '
    .plugincy-block-preview {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 16px;
        margin: 16px 0;
    }
    
    .plugincy-color-row {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    
    .plugincy-color-row__swatch {
        width: 24px;
        height: 24px;
        margin-top: 28px;
        border: 1px solid #dcdcde;
        border-radius: 50%;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.04);
    }

    .plugincy-color-row label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .plugincy-tabs .components-tab-panel__tab {
        font-size: 13px;
        font-weight: 500;
    }
    
    .plugincy-tabs .components-tab-panel__tab.active-tab {
        box-shadow: inset 0 -2px 0 0 #007cba;
    }
    
    .components-panel__body-title {
        font-size: 14px;
        font-weight: 600;
    }
    
    .components-base-control__help {
        font-size: 12px;
        font-style: italic;
        color: #757575;
    }
    
    .plugincy-shortcode-preview {
        font-family: monospace;
        font-size: 12px;
        background: #fff;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 3px;
        margin: 0;
        word-break: break-all;
        line-height: 1.4;
    }
    ';

    wp_add_inline_style('wp-edit-blocks', $custom_css);
}
add_action('enqueue_block_editor_assets', 'onepaqucpro_enqueue_block_editor_assets');

/**
 * Validate block attributes before rendering
 *
 * @param array $attributes Block attributes.
 * @return array Validated attributes.
 */
function onepaqucpro_validate_block_attributes($attributes)
{
    $validated = array();

    // Validate product_ids (comma-separated numbers)
    if (isset($attributes['product_ids'])) {
        $product_ids = sanitize_text_field($attributes['product_ids']);
        if (preg_match('/^[\d,\s]*$/', $product_ids)) {
            $validated['product_ids'] = $product_ids;
        }
    }

    // Validate category (comma-separated slugs)
    if (isset($attributes['category'])) {
        $category = sanitize_text_field($attributes['category']);
        if (preg_match('/^[a-zA-Z0-9\-_,\s]*$/', $category)) {
            $validated['category'] = $category;
        }
    }

    // Validate tags (comma-separated slugs)
    if (isset($attributes['tags'])) {
        $tags = sanitize_text_field($attributes['tags']);
        if (preg_match('/^[a-zA-Z0-9\-_,\s]*$/', $tags)) {
            $validated['tags'] = $tags;
        }
    }

    // Validate attribute (single attribute name)
    if (isset($attributes['attribute'])) {
        $attribute = sanitize_text_field($attributes['attribute']);
        if (preg_match('/^[a-zA-Z0-9\-_]*$/', $attribute)) {
            $validated['attribute'] = $attribute;
        }
    }

    // Validate terms (comma-separated terms)
    if (isset($attributes['terms'])) {
        $terms = sanitize_text_field($attributes['terms']);
        if (preg_match('/^[a-zA-Z0-9\-_,\s]*$/', $terms)) {
            $validated['terms'] = $terms;
        }
    }

    // Validate template (predefined options)
    $valid_templates = array(
        'product-table',
        'product-list',
        'product-single',
        'product-slider',
        'product-accordion',
        'product-tabs',
        'pricing-table',
        'product-selection'
    );
    if (isset($attributes['template']) && in_array($attributes['template'], $valid_templates)) {
        $validated['template'] = $attributes['template'];
    } else {
        $validated['template'] = 'product-tabs'; // Default fallback
    }

    $valid_positions = array(
        'after_description',
        'before_checkout',
        'above_checkout',
        'before_order_notes',
        'order_notes',
        'after_checkout',
        'below_checkout',
    );
    if (isset($attributes['position']) && in_array($attributes['position'], $valid_positions, true)) {
        $validated['position'] = $attributes['position'];
    } else {
        $validated['position'] = 'after_description';
    }

    if (isset($attributes['product_label'])) {
        $validated['product_label'] = sanitize_text_field($attributes['product_label']);
    }

    if (isset($attributes['variation_label'])) {
        $validated['variation_label'] = sanitize_text_field($attributes['variation_label']);
    }

    if (isset($attributes['updating_selection_text'])) {
        $validated['updating_selection_text'] = sanitize_text_field($attributes['updating_selection_text']);
    }

    $validated['show_images'] = !empty($attributes['show_images']);

    $valid_product_layouts = array('select_dropdown', 'card_dropdown', 'cards');
    if (isset($attributes['product_layout']) && in_array($attributes['product_layout'], $valid_product_layouts, true)) {
        $validated['product_layout'] = $attributes['product_layout'];
    } else {
        $validated['product_layout'] = 'select_dropdown';
    }

    return array_merge(is_array($attributes) ? $attributes : [], is_array($validated) ? $validated : []);
}


/**
 * Add custom category for Plugincy blocks
 */
function onepaqucpro_block_categories($categories, $post)
{
    // Create the new category array
    $new_category = array(
        'slug' => 'plugincy',
        'title' => esc_html__('Plugincy', 'one-page-quick-checkout-for-woocommerce-pro'),
        'icon'  => 'plugincy',
    );

    // Add the new category to the beginning of the categories array
    array_unshift($categories, $new_category);

    return $categories;
}
add_filter('block_categories_all', 'onepaqucpro_block_categories', 0, 2);
