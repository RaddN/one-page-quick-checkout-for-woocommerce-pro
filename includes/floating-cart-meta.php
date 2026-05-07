<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function onepaqucpro_get_floating_cart_reserved_cart_item_keys()
{
    $reserved_keys = array(
        'key',
        'product_id',
        'variation_id',
        'variation',
        'quantity',
        'data',
        'data_hash',
        'line_tax_data',
        'line_subtotal',
        'line_subtotal_tax',
        'line_total',
        'line_tax',
    );

    return apply_filters('onepaqucpro_floating_cart_reserved_cart_item_keys', $reserved_keys);
}

function onepaqucpro_maybe_load_floating_cart_context()
{
    if (!function_exists('WC') || !WC()) {
        return;
    }

    if (WC()->cart || !function_exists('wc_load_cart')) {
        return;
    }

    try {
        wc_load_cart();
    } catch (Throwable $e) {
        return;
    }
}

function onepaqucpro_normalize_floating_cart_meta_identifier($identifier)
{
    $identifier = trim((string) $identifier);
    if ($identifier === '') {
        return '';
    }

    $raw_key = sanitize_key($identifier);
    $title_key = sanitize_title($identifier);

    if (preg_match('/\s/', $identifier)) {
        return $title_key !== '' ? $title_key : $raw_key;
    }

    return $raw_key !== '' ? $raw_key : $title_key;
}

function onepaqucpro_get_floating_cart_meta_aliases($identifier, $label = '')
{
    $aliases = array(
        onepaqucpro_normalize_floating_cart_meta_identifier($identifier),
        sanitize_title((string) $identifier),
        sanitize_key((string) $identifier),
    );

    if ($label !== '') {
        $aliases[] = onepaqucpro_normalize_floating_cart_meta_identifier($label);
        $aliases[] = sanitize_title((string) $label);
        $aliases[] = sanitize_key((string) $label);
    }

    return array_values(array_unique(array_filter($aliases)));
}

function onepaqucpro_get_hidden_floating_cart_meta_keys()
{
    return apply_filters('onepaqucpro_hidden_floating_cart_meta_keys', array(
        'mulopimfwc_location',
    ));
}

function onepaqucpro_is_hidden_floating_cart_meta_key($meta_key)
{
    $meta_key = onepaqucpro_normalize_floating_cart_meta_identifier($meta_key);
    $hidden_keys = array_map('onepaqucpro_normalize_floating_cart_meta_identifier', onepaqucpro_get_hidden_floating_cart_meta_keys());

    return in_array($meta_key, $hidden_keys, true);
}

function onepaqucpro_format_floating_cart_meta_label($meta_key)
{
    $label = trim((string) $meta_key);
    $label = preg_replace('/^attribute_/', '', $label);
    $label = str_replace(array('_', '-'), ' ', $label);
    $label = preg_replace('/\s+/', ' ', $label);

    return ucwords(trim($label));
}

function onepaqucpro_decode_floating_cart_meta_rules($value, $available_options = array())
{
    $decoded = null;

    if (is_string($value)) {
        $value = trim($value);
        if ($value !== '' && in_array(substr($value, 0, 1), array('[', '{'), true)) {
            $decoded = json_decode(wp_unslash($value), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = null;
            }
        }
    } elseif (is_array($value)) {
        $decoded = $value;
    }

    if ($decoded === null) {
        $parts = array_filter(array_map('trim', explode(',', (string) $value)));
        $decoded = array();
        foreach ($parts as $part) {
            $decoded[] = array(
                'key' => $part,
            );
        }
    }

    if (isset($decoded['key'])) {
        $decoded = array($decoded);
    }

    $rules = array();
    foreach ((array) $decoded as $item) {
        if (is_string($item) || is_numeric($item)) {
            $key = (string) $item;
            $title = '';
        } elseif (is_array($item)) {
            $key = isset($item['key']) ? (string) $item['key'] : '';
            if ($key === '' && isset($item['id'])) {
                $key = (string) $item['id'];
            }
            if ($key === '' && isset($item['meta'])) {
                $key = (string) $item['meta'];
            }
            $title = isset($item['title']) ? (string) $item['title'] : '';
        } else {
            continue;
        }

        $key = onepaqucpro_normalize_floating_cart_meta_identifier($key);
        if ($key === '' || onepaqucpro_is_hidden_floating_cart_meta_key($key)) {
            continue;
        }

        $title = sanitize_text_field($title);
        if ($title === '') {
            $title = isset($available_options[$key])
                ? preg_replace('/\s+\([^)]+\)$/', '', wp_strip_all_tags((string) $available_options[$key]))
                : onepaqucpro_format_floating_cart_meta_label($key);
        }

        $rules[] = array(
            'key'   => $key,
            'title' => $title,
        );
    }

    return $rules;
}

function onepaqucpro_encode_floating_cart_meta_rules($rules)
{
    $normalized = onepaqucpro_decode_floating_cart_meta_rules($rules);

    return wp_json_encode($normalized);
}

function onepaqucpro_get_floating_cart_meta_rules_option($option_name)
{
    return onepaqucpro_decode_floating_cart_meta_rules(get_option($option_name, ''));
}

function onepaqucpro_get_floating_cart_meta_display_value($value)
{
    if (is_bool($value)) {
        return $value ? __('Yes', 'one-page-quick-checkout-for-woocommerce-pro') : __('No', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    if (is_scalar($value)) {
        return trim((string) $value);
    }

    if (is_array($value)) {
        $parts = array();
        array_walk_recursive($value, function ($item) use (&$parts) {
            if (is_bool($item)) {
                $parts[] = $item ? __('Yes', 'one-page-quick-checkout-for-woocommerce-pro') : __('No', 'one-page-quick-checkout-for-woocommerce-pro');
                return;
            }

            if (is_scalar($item)) {
                $item = trim((string) $item);
                if ($item !== '') {
                    $parts[] = $item;
                }
            }
        });

        return implode(', ', array_unique($parts));
    }

    return '';
}

function onepaqucpro_add_floating_cart_meta_row(&$rows, $identifier, $label, $display, $source = 'raw')
{
    $identifier = onepaqucpro_normalize_floating_cart_meta_identifier($identifier);
    $label = wp_strip_all_tags((string) $label);

    if ($identifier === '' || $label === '') {
        return;
    }

    $display = is_scalar($display) ? (string) $display : onepaqucpro_get_floating_cart_meta_display_value($display);
    $plain_value = trim(wp_strip_all_tags($display));

    $rows[] = array(
        'id'          => $identifier,
        'label'       => $label,
        'display'     => $display,
        'plain_value' => $plain_value,
        'source'      => $source,
        'aliases'     => onepaqucpro_get_floating_cart_meta_aliases($identifier, $label),
    );
}

function onepaqucpro_get_floating_cart_cart_item_meta_rows($cart_item)
{
    $cart_item = is_array($cart_item) ? $cart_item : array();
    $rows = array();

    if (function_exists('wc_get_item_data')) {
        foreach ((array) wc_get_item_data($cart_item) as $row) {
            $label = isset($row['key']) ? wp_strip_all_tags((string) $row['key']) : '';
            if ($label === '') {
                continue;
            }

            $display = '';
            if (isset($row['display']) && is_scalar($row['display']) && trim((string) $row['display']) !== '') {
                $display = (string) $row['display'];
            } elseif (isset($row['value'])) {
                $display = onepaqucpro_get_floating_cart_meta_display_value($row['value']);
            } elseif (isset($row['display'])) {
                $display = onepaqucpro_get_floating_cart_meta_display_value($row['display']);
            }

            onepaqucpro_add_floating_cart_meta_row($rows, sanitize_title($label), $label, $display, 'display');
        }
    }

    $reserved_keys = onepaqucpro_get_floating_cart_reserved_cart_item_keys();
    foreach ($cart_item as $meta_key => $value) {
        $meta_key = (string) $meta_key;
        if ($meta_key === '' || in_array($meta_key, $reserved_keys, true)) {
            continue;
        }

        $display = onepaqucpro_get_floating_cart_meta_display_value($value);
        if ($display === '') {
            continue;
        }

        onepaqucpro_add_floating_cart_meta_row(
            $rows,
            $meta_key,
            onepaqucpro_format_floating_cart_meta_label($meta_key),
            $display,
            'raw'
        );
    }

    return apply_filters('onepaqucpro_floating_cart_cart_item_meta_rows', $rows, $cart_item);
}

function onepaqucpro_floating_cart_meta_row_matches($row, $needles)
{
    $needles = array_filter((array) $needles);
    if (empty($needles)) {
        return false;
    }

    $aliases = isset($row['aliases']) && is_array($row['aliases']) ? $row['aliases'] : array();
    if (isset($row['id'])) {
        $aliases[] = $row['id'];
    }

    return (bool) array_intersect(array_unique(array_filter($aliases)), $needles);
}

function onepaqucpro_get_floating_cart_cart_item_meta_value($cart_item, $identifier)
{
    $needles = onepaqucpro_get_floating_cart_meta_aliases($identifier);
    foreach (onepaqucpro_get_floating_cart_cart_item_meta_rows($cart_item) as $row) {
        if (!onepaqucpro_floating_cart_meta_row_matches($row, $needles)) {
            continue;
        }

        if (!empty($row['plain_value'])) {
            return $row['plain_value'];
        }

        if (!empty($row['display'])) {
            return trim(wp_strip_all_tags((string) $row['display']));
        }
    }

    return '';
}
