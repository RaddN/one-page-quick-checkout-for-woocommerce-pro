<?php
/**
 * Frontend QC discovery output for Plugincy automation.
 *
 * Visit any frontend URL with ?plugincydebug=true to expose representative
 * WooCommerce archive/product URLs needed by the QC runner.
 */

if (! defined('ABSPATH')) {
    exit;
}

function onepaqucpro_qc_is_debug_request()
{
    if (is_admin() || wp_doing_ajax()) {
        return false;
    }

    if (! isset($_GET['plugincydebug'])) {
        return false;
    }

    $value = sanitize_text_field(wp_unslash($_GET['plugincydebug']));
    return in_array(strtolower($value), array('1', 'true', 'yes', 'on'), true);
}

function onepaqucpro_qc_add_debug_query($url)
{
    if (empty($url) || is_wp_error($url)) {
        return null;
    }

    return add_query_arg('plugincydebug', 'true', $url);
}

function onepaqucpro_qc_normalize_url($url)
{
    if (empty($url) || is_wp_error($url)) {
        return null;
    }

    return esc_url_raw($url);
}

function onepaqucpro_qc_term_payload($term, $taxonomy = '')
{
    if (! $term || is_wp_error($term)) {
        return null;
    }

    $term_url = get_term_link($term, $taxonomy ?: $term->taxonomy);

    if (is_wp_error($term_url)) {
        return null;
    }

    return array(
        'id'        => (int) $term->term_id,
        'name'      => $term->name,
        'slug'      => $term->slug,
        'taxonomy'  => $term->taxonomy,
        'parent'    => (int) $term->parent,
        'url'       => onepaqucpro_qc_normalize_url($term_url),
        'debugUrl'  => onepaqucpro_qc_add_debug_query($term_url),
    );
}

function onepaqucpro_qc_get_terms($args)
{
    $terms = get_terms($args);

    if (is_wp_error($terms) || empty($terms)) {
        return array();
    }

    return $terms;
}

function onepaqucpro_qc_get_sample_term($taxonomy, $parent = null, $hide_empty = true)
{
    $args = array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => $hide_empty,
        'number'     => 1,
        'orderby'    => 'count',
        'order'      => 'DESC',
    );

    if (null !== $parent) {
        $args['parent'] = (int) $parent;
    }

    $terms = onepaqucpro_qc_get_terms($args);
    return ! empty($terms) ? $terms[0] : null;
}

function onepaqucpro_qc_get_taxonomy_archive_payload($taxonomy_name, $taxonomy_object)
{
    $is_hierarchical = is_taxonomy_hierarchical($taxonomy_name);
    $parent_term = null;
    $child_term = null;
    $sample_term = onepaqucpro_qc_get_sample_term($taxonomy_name);

    if (! $sample_term) {
        $sample_term = onepaqucpro_qc_get_sample_term($taxonomy_name, null, false);
    }

    if ($is_hierarchical) {
        $parent_candidates = onepaqucpro_qc_get_terms(array(
            'taxonomy'   => $taxonomy_name,
            'hide_empty' => true,
            'parent'     => 0,
            'number'     => 50,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ));

        if (empty($parent_candidates)) {
            $parent_candidates = onepaqucpro_qc_get_terms(array(
                'taxonomy'   => $taxonomy_name,
                'hide_empty' => false,
                'parent'     => 0,
                'number'     => 50,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ));
        }

        foreach ($parent_candidates as $candidate) {
            $candidate_child = onepaqucpro_qc_get_sample_term($taxonomy_name, $candidate->term_id);

            if (! $candidate_child) {
                $candidate_child = onepaqucpro_qc_get_sample_term($taxonomy_name, $candidate->term_id, false);
            }

            if (! $parent_term) {
                $parent_term = $candidate;
            }

            if ($candidate_child) {
                $parent_term = $candidate;
                $child_term = $candidate_child;
                break;
            }
        }
    }

    return array(
        'name'         => $taxonomy_name,
        'label'        => isset($taxonomy_object->labels->singular_name) ? $taxonomy_object->labels->singular_name : $taxonomy_object->label,
        'hierarchical' => (bool) $is_hierarchical,
        'sampleTerm'   => onepaqucpro_qc_term_payload($sample_term, $taxonomy_name),
        'parentTerm'   => onepaqucpro_qc_term_payload($parent_term, $taxonomy_name),
        'childTerm'    => onepaqucpro_qc_term_payload($child_term, $taxonomy_name),
    );
}

function onepaqucpro_qc_get_public_attribute_taxonomy_names()
{
    if (! function_exists('wc_get_attribute_taxonomies') || ! function_exists('wc_attribute_taxonomy_name')) {
        return array();
    }

    $public_taxonomies = array();
    $attributes = wc_get_attribute_taxonomies();

    if (empty($attributes)) {
        return $public_taxonomies;
    }

    foreach ($attributes as $attribute) {
        if (empty($attribute->attribute_name) || empty($attribute->attribute_public)) {
            continue;
        }

        $taxonomy_name = wc_attribute_taxonomy_name($attribute->attribute_name);

        if (taxonomy_exists($taxonomy_name)) {
            $public_taxonomies[] = $taxonomy_name;
        }
    }

    return array_values(array_unique($public_taxonomies));
}

function onepaqucpro_qc_is_brand_taxonomy_name($taxonomy_name)
{
    $brand_taxonomies = array(
        'product_brand',
        'pwb-brand',
        'yith_product_brand',
        'berocket_brand',
        'br_product_brand',
        'brand',
        'pa_brand',
    );

    return in_array($taxonomy_name, $brand_taxonomies, true) || false !== stripos($taxonomy_name, 'brand');
}

function onepaqucpro_qc_find_public_attribute_taxonomy($taxonomy_archives)
{
    foreach ($taxonomy_archives as $taxonomy) {
        if (0 === strpos($taxonomy['name'], 'pa_') && ! onepaqucpro_qc_is_brand_taxonomy_name($taxonomy['name']) && onepaqucpro_qc_first_term_from_taxonomy_payload($taxonomy)) {
            return $taxonomy;
        }
    }

    foreach ($taxonomy_archives as $taxonomy) {
        if (0 === strpos($taxonomy['name'], 'pa_') && onepaqucpro_qc_first_term_from_taxonomy_payload($taxonomy)) {
            return $taxonomy;
        }
    }

    return null;
}

function onepaqucpro_qc_get_product_payload($type)
{
    if (! function_exists('wc_get_products')) {
        return null;
    }

    $products = wc_get_products(array(
        'status'  => 'publish',
        'type'    => $type,
        'limit'   => 1,
        'orderby' => 'date',
        'order'   => 'DESC',
        'return'  => 'objects',
    ));

    if (empty($products)) {
        return null;
    }

    $product = $products[0];
    $url = get_permalink($product->get_id());

    return array(
        'id'       => $product->get_id(),
        'name'     => $product->get_name(),
        'type'     => $product->get_type(),
        'url'      => onepaqucpro_qc_normalize_url($url),
        'debugUrl' => onepaqucpro_qc_add_debug_query($url),
    );
}

function onepaqucpro_qc_get_one_page_checkout_product_payload()
{
    if (! function_exists('wc_get_products')) {
        return null;
    }

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 20,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'meta_key'   => '_one_page_checkout',
        'meta_value' => 'yes',
        'return'     => 'objects',
    ));

    if (empty($products)) {
        return null;
    }

    foreach ($products as $product) {
        if (! $product || ! is_a($product, 'WC_Product')) {
            continue;
        }

        if (! $product->is_purchasable() || (! $product->is_in_stock() && ! $product->is_on_backorder())) {
            continue;
        }

        $url = get_permalink($product->get_id());

        return array(
            'id'       => $product->get_id(),
            'name'     => $product->get_name(),
            'type'     => $product->get_type(),
            'url'      => onepaqucpro_qc_normalize_url($url),
            'debugUrl' => onepaqucpro_qc_add_debug_query($url),
        );
    }

    return null;
}

function onepaqucpro_qc_get_page_payload($page_id, $label = '')
{
    $page_id = absint($page_id);

    if (! $page_id) {
        return null;
    }

    $url = get_permalink($page_id);

    if (! $url || is_wp_error($url)) {
        return null;
    }

    return array(
        'id'       => $page_id,
        'label'    => $label ?: get_the_title($page_id),
        'name'     => get_the_title($page_id),
        'url'      => onepaqucpro_qc_normalize_url($url),
        'debugUrl' => onepaqucpro_qc_add_debug_query($url),
    );
}

function onepaqucpro_qc_page_uses_one_page_checkout($post)
{
    if (! $post || empty($post->ID)) {
        return false;
    }

    $content = isset($post->post_content) ? (string) $post->post_content : '';

    if (function_exists('has_shortcode')) {
        if (has_shortcode($content, 'plugincy_one_page_checkout') || has_shortcode($content, 'onepaquc_checkout')) {
            return true;
        }
    }

    if (function_exists('has_block')) {
        if (has_block('plugincy/one-page-checkout', $post) || has_block('wc/one-page-checkout', $post)) {
            return true;
        }
    }

    $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
    if (is_string($elementor_data) && '' !== $elementor_data) {
        if (false !== strpos($elementor_data, '"widgetType":"onepaquc_checkout"') || false !== strpos($elementor_data, '"widgetType":"plugincy"')) {
            return true;
        }
    }

    return false;
}

function onepaqucpro_qc_get_one_page_checkout_pages()
{
    $pages = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 30,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $payloads = array();

    foreach ($pages as $page) {
        if (! onepaqucpro_qc_page_uses_one_page_checkout($page)) {
            continue;
        }

        $payload = onepaqucpro_qc_get_page_payload($page->ID, get_the_title($page->ID));
        if ($payload) {
            $payloads[] = $payload;
        }
    }

    return $payloads;
}

function onepaqucpro_qc_find_taxonomy_by_name_fragment($taxonomy_archives, $fragments)
{
    foreach ($taxonomy_archives as $taxonomy) {
        $name = strtolower($taxonomy['name']);
        $label = strtolower($taxonomy['label']);

        foreach ($fragments as $fragment) {
            if (false !== strpos($name, $fragment) || false !== strpos($label, $fragment)) {
                return $taxonomy;
            }
        }
    }

    return null;
}

function onepaqucpro_qc_find_brand_taxonomy($taxonomy_archives)
{
    $preferred_names = array(
        'product_brand',
        'pwb-brand',
        'yith_product_brand',
        'berocket_brand',
        'pa_brand',
    );

    foreach ($preferred_names as $preferred_name) {
        foreach ($taxonomy_archives as $taxonomy) {
            if ($preferred_name === $taxonomy['name'] && onepaqucpro_qc_first_term_from_taxonomy_payload($taxonomy)) {
                return $taxonomy;
            }
        }
    }

    return onepaqucpro_qc_find_taxonomy_by_name_fragment($taxonomy_archives, array('brand'));
}

function onepaqucpro_qc_first_term_from_taxonomy_payload($taxonomy)
{
    if (! $taxonomy) {
        return null;
    }

    if (! empty($taxonomy['sampleTerm'])) {
        return $taxonomy['sampleTerm'];
    }

    if (! empty($taxonomy['parentTerm'])) {
        return $taxonomy['parentTerm'];
    }

    if (! empty($taxonomy['childTerm'])) {
        return $taxonomy['childTerm'];
    }

    return null;
}

function onepaqucpro_qc_collect_debug_pages()
{
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : null;
    $cart_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('cart') : 0;
    $checkout_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('checkout') : 0;
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $taxonomy_objects = get_object_taxonomies('product', 'objects');
    $taxonomy_archives = array();
    $public_attribute_taxonomies = onepaqucpro_qc_get_public_attribute_taxonomy_names();

    foreach ($taxonomy_objects as $taxonomy_name => $taxonomy_object) {
        $is_public = ! empty($taxonomy_object->public) || ! empty($taxonomy_object->publicly_queryable);
        $is_attribute = 0 === strpos($taxonomy_name, 'pa_');

        if ($is_attribute && ! in_array($taxonomy_name, $public_attribute_taxonomies, true)) {
            continue;
        }

        if (! $is_public && ! $is_attribute) {
            continue;
        }

        $taxonomy_payload = onepaqucpro_qc_get_taxonomy_archive_payload($taxonomy_name, $taxonomy_object);

        $taxonomy_archives[] = $taxonomy_payload;
    }

    usort($taxonomy_archives, function ($left, $right) {
        return strcasecmp($left['name'], $right['name']);
    });

    $category_taxonomy = null;
    foreach ($taxonomy_archives as $taxonomy) {
        if ('product_cat' === $taxonomy['name']) {
            $category_taxonomy = $taxonomy;
            break;
        }
    }

    $tag_taxonomy = null;
    foreach ($taxonomy_archives as $taxonomy) {
        if ('product_tag' === $taxonomy['name']) {
            $tag_taxonomy = $taxonomy;
            break;
        }
    }

    $brand_taxonomy = onepaqucpro_qc_find_brand_taxonomy($taxonomy_archives);
    $attribute_taxonomy = onepaqucpro_qc_find_public_attribute_taxonomy($taxonomy_archives);
    $cart_page = onepaqucpro_qc_get_page_payload($cart_page_id, 'Cart page');
    $checkout_page = onepaqucpro_qc_get_page_payload($checkout_page_id, 'Checkout page');
    $one_page_checkout_pages = onepaqucpro_qc_get_one_page_checkout_pages();

    $required_pages = array(
        'shop'           => array(
            'label'    => 'Shop page',
            'url'      => onepaqucpro_qc_normalize_url($shop_url),
            'debugUrl' => onepaqucpro_qc_add_debug_query($shop_url),
        ),
        'parentCategory' => array(
            'label' => 'Parent product category archive',
            'term'  => ! empty($category_taxonomy['parentTerm']) ? $category_taxonomy['parentTerm'] : onepaqucpro_qc_first_term_from_taxonomy_payload($category_taxonomy),
        ),
        'childCategory'  => array(
            'label' => 'Child product category archive',
            'term'  => ! empty($category_taxonomy['childTerm']) ? $category_taxonomy['childTerm'] : null,
        ),
        'brand'          => array(
            'label' => 'Brand archive',
            'term'  => onepaqucpro_qc_first_term_from_taxonomy_payload($brand_taxonomy),
        ),
        'tag'            => array(
            'label' => 'Product tag archive',
            'term'  => onepaqucpro_qc_first_term_from_taxonomy_payload($tag_taxonomy),
        ),
        'attributeTerm'  => array(
            'label' => 'Product attribute term archive',
            'term'  => onepaqucpro_qc_first_term_from_taxonomy_payload($attribute_taxonomy),
        ),
        'simpleProduct'  => array(
            'label'   => 'Simple product page',
            'product' => onepaqucpro_qc_get_product_payload('simple'),
        ),
        'variableProduct' => array(
            'label'   => 'Variable product page',
            'product' => onepaqucpro_qc_get_product_payload('variable'),
        ),
        'externalProduct' => array(
            'label'   => 'External/Affiliate product page',
            'product' => onepaqucpro_qc_get_product_payload('external'),
        ),
        'groupedProduct' => array(
            'label'   => 'Grouped product page',
            'product' => onepaqucpro_qc_get_product_payload('grouped'),
        ),
        'onePageCheckoutProduct' => array(
            'label'   => 'Single product with one-page checkout',
            'product' => onepaqucpro_qc_get_one_page_checkout_product_payload(),
        ),
        'cart'           => array(
            'label'    => 'Cart page',
            'url'      => $cart_page ? $cart_page['url'] : null,
            'debugUrl' => $cart_page ? $cart_page['debugUrl'] : null,
        ),
        'checkout'       => array(
            'label'    => 'Checkout page',
            'url'      => $checkout_page ? $checkout_page['url'] : null,
            'debugUrl' => $checkout_page ? $checkout_page['debugUrl'] : null,
        ),
    );

    $missing = array();
    foreach ($required_pages as $key => $page) {
        $has_url = ! empty($page['url'])
            || ! empty($page['debugUrl'])
            || ! empty($page['term']['url'])
            || ! empty($page['term']['debugUrl'])
            || ! empty($page['product']['url'])
            || ! empty($page['product']['debugUrl']);

        if (! $has_url) {
            $missing[] = array(
                'key'   => $key,
                'label' => $page['label'],
            );
        }
    }

    return array(
        'plugin'           => 'one-page-quick-checkout-for-woocommerce-pro',
        'generatedAt'      => gmdate('c'),
        'currentUrl'       => onepaqucpro_qc_normalize_url(home_url($request_uri)),
        'requiredPages'    => $required_pages,
        'taxonomyArchives' => $taxonomy_archives,
        'onePageCheckoutPages' => $one_page_checkout_pages,
        'missing'          => $missing,
    );
}

function onepaqucpro_qc_get_page_url_from_payload($page)
{
    if (! empty($page['debugUrl'])) {
        return $page['debugUrl'];
    }

    if (! empty($page['term']['debugUrl'])) {
        return $page['term']['debugUrl'];
    }

    if (! empty($page['product']['debugUrl'])) {
        return $page['product']['debugUrl'];
    }

    if (! empty($page['url'])) {
        return $page['url'];
    }

    if (! empty($page['term']['url'])) {
        return $page['term']['url'];
    }

    if (! empty($page['product']['url'])) {
        return $page['product']['url'];
    }

    return null;
}

function onepaqucpro_qc_get_page_name_from_payload($page)
{
    if (! empty($page['term']['name'])) {
        return $page['term']['name'];
    }

    if (! empty($page['product']['name'])) {
        return $page['product']['name'];
    }

    return '';
}

function onepaqucpro_qc_render_debug_pages()
{
    if (! onepaqucpro_qc_is_debug_request()) {
        return;
    }

    $debug_data = onepaqucpro_qc_collect_debug_pages();
    $json = wp_json_encode($debug_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    <section id="plugincy-qc-debug" class="plugincy-qc-debug" data-plugincy-qc-debug="true">
        <style>
            #plugincy-qc-debug {
                margin: 32px auto;
                padding: 20px;
                max-width: 1180px;
                border: 2px solid #2271b1;
                border-radius: 6px;
                background: #fff;
                color: #1d2327;
                font: 14px/1.45 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
                clear: both;
                position: relative;
                z-index: 999999;
            }

            #plugincy-qc-debug h2,
            #plugincy-qc-debug h3 {
                margin: 0 0 12px;
                color: #1d2327;
            }

            #plugincy-qc-debug h3 {
                margin-top: 20px;
            }

            #plugincy-qc-debug table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            #plugincy-qc-debug th,
            #plugincy-qc-debug td {
                border: 1px solid #dcdcde;
                padding: 8px;
                text-align: left;
                vertical-align: top;
            }

            #plugincy-qc-debug th {
                background: #f6f7f7;
            }

            #plugincy-qc-debug a {
                color: #135e96;
                word-break: break-all;
            }

            #plugincy-qc-debug .plugincy-qc-missing {
                color: #b32d2e;
                font-weight: 600;
            }
        </style>
        <h2>Plugincy QC Debug Pages</h2>
        <p>Use these URLs for automated QC. The link targets keep <code>plugincydebug=true</code> so page-level debug data remains available.</p>

        <h3>Required Pages</h3>
        <table>
            <thead>
                <tr>
                    <th>Target</th>
                    <th>Selected item</th>
                    <th>Debug URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($debug_data['requiredPages'] as $key => $page) : ?>
                    <?php $url = onepaqucpro_qc_get_page_url_from_payload($page); ?>
                    <tr data-qc-target="<?php echo esc_attr($key); ?>">
                        <td><?php echo esc_html($page['label']); ?></td>
                        <td><?php echo esc_html(onepaqucpro_qc_get_page_name_from_payload($page)); ?></td>
                        <td>
                            <?php if ($url) : ?>
                                <a data-plugincy-qc-url="required" data-qc-key="<?php echo esc_attr($key); ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($url); ?></a>
                            <?php else : ?>
                                <span class="plugincy-qc-missing">Missing suitable public URL</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Product Taxonomy Archives</h3>
        <table>
            <thead>
                <tr>
                    <th>Taxonomy</th>
                    <th>Sample term</th>
                    <th>Parent term</th>
                    <th>Child term</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($debug_data['taxonomyArchives'] as $taxonomy) : ?>
                    <tr data-qc-taxonomy="<?php echo esc_attr($taxonomy['name']); ?>">
                        <td>
                            <strong><?php echo esc_html($taxonomy['label']); ?></strong><br>
                            <code><?php echo esc_html($taxonomy['name']); ?></code>
                        </td>
                        <?php foreach (array('sampleTerm', 'parentTerm', 'childTerm') as $term_key) : ?>
                            <td>
                                <?php if (! empty($taxonomy[$term_key]['debugUrl'])) : ?>
                                    <a data-plugincy-qc-url="taxonomy" data-taxonomy="<?php echo esc_attr($taxonomy['name']); ?>" data-term-role="<?php echo esc_attr($term_key); ?>" href="<?php echo esc_url($taxonomy[$term_key]['debugUrl']); ?>">
                                        <?php echo esc_html($taxonomy[$term_key]['name']); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="plugincy-qc-missing">Not found</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>One Page Checkout Pages</h3>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Debug URL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($debug_data['onePageCheckoutPages'])) : ?>
                    <?php foreach ($debug_data['onePageCheckoutPages'] as $checkout_page) : ?>
                        <tr data-qc-one-page-checkout="<?php echo esc_attr($checkout_page['id']); ?>">
                            <td><?php echo esc_html($checkout_page['name']); ?></td>
                            <td>
                                <a data-plugincy-qc-url="one-page-checkout" href="<?php echo esc_url($checkout_page['debugUrl']); ?>"><?php echo esc_html($checkout_page['debugUrl']); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="2"><span class="plugincy-qc-missing">No published page using the Plugincy one-page checkout shortcode/block was found.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <script id="plugincy-qc-debug-data" type="application/json"><?php echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
        <script>
            (function() {
                var data = <?php echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
                window.plugincyQcDebug = data;

                if (window.console && typeof window.console.log === 'function') {
                    window.console.log('Plugincy QC Debug Pages', data);
                }

                if (typeof window.plugincydebugLog === 'function') {
                    window.plugincydebugLog('Plugincy QC Debug Pages', data);
                }
            })();
        </script>
    </section>
    <?php
}

add_action('wp_footer', 'onepaqucpro_qc_render_debug_pages', 999);
