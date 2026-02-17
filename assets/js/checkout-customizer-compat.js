(function () {
    'use strict';

    function hasOwn(obj, key) {
        return Object.prototype.hasOwnProperty.call(obj, key);
    }

    function toArray(list) {
        return Array.prototype.slice.call(list || []);
    }

    function isVisible(value) {
        if (value === false || value === 0 || value === '0' || value === 'false') {
            return false;
        }
        return !!value;
    }

    function unique(nodes) {
        var map = [];
        nodes.forEach(function (node) {
            if (node && map.indexOf(node) === -1) {
                map.push(node);
            }
        });
        return map;
    }

    function parseConfig() {
        var settings = window.onepaqucpro_rmsgValue &&
            window.onepaqucpro_rmsgValue.plugincy_all_settings;

        if (!settings || !settings.checkout_form_setup) {
            return null;
        }

        if (typeof settings.checkout_form_setup === 'string') {
            try {
                return JSON.parse(settings.checkout_form_setup);
            } catch (e) {
                return null;
            }
        }

        if (typeof settings.checkout_form_setup === 'object') {
            return settings.checkout_form_setup;
        }

        return null;
    }

    var config = parseConfig();
    if (!config || typeof config !== 'object') {
        return;
    }

    var billingMap = {
        'first-name': 'first_name',
        'last-name': 'last_name',
        'email': 'email',
        'phone': 'phone',
        'country': 'country',
        'address': 'address_1',
        'address2': 'address_2',
        'city': 'city',
        'state': 'state',
        'postcode': 'postcode',
        'company': 'company'
    };

    var shippingMap = {
        'shipping-first-name': 'first_name',
        'shipping-last-name': 'last_name',
        'shipping-country': 'country',
        'shipping-address': 'address_1',
        'shipping-address2': 'address_2',
        'shipping-city': 'city',
        'shipping-state': 'state',
        'shipping-postcode': 'postcode',
        'shipping-company': 'company'
    };

    function fieldSelectors(prefix, key) {
        return [
            '#' + prefix + '_' + key,
            '#' + prefix + '-' + key,
            '[name="' + prefix + '_' + key + '"]',
            '[name="' + prefix + '-' + key + '"]',
            '[name="' + prefix + '[' + key + ']"]'
        ].join(',');
    }

    function findFieldElements(prefix, key) {
        var selectors = fieldSelectors(prefix, key);
        var fields = toArray(document.querySelectorAll(selectors));

        if (prefix === 'billing' && key === 'email') {
            fields = fields.concat(toArray(document.querySelectorAll('#email,[name="email"]')));
        }

        if (prefix === 'billing' && key === 'phone') {
            fields = fields.concat(toArray(document.querySelectorAll('#phone,[name="phone"]')));
        }

        return unique(fields);
    }

    function getFieldContainers(field, key) {
        var selectors = [
            '.form-row',
            '.woocommerce-form-row',
            '.wc-block-components-form-field',
            '.wc-block-components-text-input',
            '.wc-block-components-select',
            '.wc-block-components-combobox',
            '.wc-block-components-checkbox',
            '.wc-block-components-address-form__' + key
        ];

        var containers = selectors
            .map(function (selector) {
                return field.closest(selector);
            })
            .filter(Boolean);

        if (!containers.length && field.parentElement) {
            containers.push(field.parentElement);
        }

        return unique(containers);
    }

    function isBlocksElement(element) {
        if (!element || typeof element.closest !== 'function') {
            return false;
        }

        return !!element.closest(
            '.wp-block-woocommerce-checkout, .wc-block-components-form, .wc-block-checkout__form, ' +
            '.wc-block-components-address-form, .wc-block-components-text-input, ' +
            '.wc-block-components-select-input, .wc-block-components-combobox-control, ' +
            '.wc-block-components-textarea'
        );
    }

    function setElementsVisible(elements, visible) {
        elements.forEach(function (element) {
            if (!element) {
                return;
            }

            if (visible) {
                if (hasOwn(element.dataset, 'onepaqucDisplay')) {
                    element.style.display = element.dataset.onepaqucDisplay;
                } else {
                    element.style.removeProperty('display');
                }
                return;
            }

            if (!hasOwn(element.dataset, 'onepaqucDisplay')) {
                element.dataset.onepaqucDisplay = element.style.display || '';
            }
            element.style.display = 'none';
        });
    }

    function getLabel(field) {
        if (!field) {
            return null;
        }

        if (field.id) {
            var byFor = document.querySelector('label[for="' + field.id.replace(/"/g, '\\"') + '"]');
            if (byFor) {
                return byFor;
            }
        }

        var wrapper = field.closest(
            '.form-row, .woocommerce-form-row, .wc-block-components-form-field, ' +
            '.wc-block-components-text-input, .wc-block-components-select, ' +
            '.wc-block-components-combobox, .wc-block-components-checkbox'
        );

        return wrapper ? wrapper.querySelector('label') : null;
    }

    function setLabelText(field, labelText, required) {
        if (!labelText) {
            return;
        }

        var label = getLabel(field);
        if (!label) {
            return;
        }

        var suffix = '';
        if (required === true) {
            suffix = ' *';
        } else if (required === null || required === undefined) {
            var original = (label.textContent || '').trim();
            if (/\*\s*$/.test(original)) {
                suffix = ' *';
            }
        }

        label.textContent = labelText + suffix;
    }

    function applyFieldConfig(prefix, key, fieldConfig) {
        if (!fieldConfig || typeof fieldConfig !== 'object') {
            return;
        }

        var elements = findFieldElements(prefix, key);
        if (!elements.length) {
            return;
        }

        elements.forEach(function (field) {
            if (hasOwn(fieldConfig, 'placeholder') && fieldConfig.placeholder !== undefined && fieldConfig.placeholder !== null) {
                if (isBlocksElement(field)) {
                    field.removeAttribute('placeholder');
                } else {
                    field.setAttribute('placeholder', fieldConfig.placeholder);
                }
            }

            if (hasOwn(fieldConfig, 'required')) {
                var isRequired = !!fieldConfig.required;
                field.required = isRequired;
                if (isRequired) {
                    field.setAttribute('aria-required', 'true');
                } else {
                    field.removeAttribute('aria-required');
                }
            }

            if (hasOwn(fieldConfig, 'label')) {
                setLabelText(field, fieldConfig.label, hasOwn(fieldConfig, 'required') ? !!fieldConfig.required : null);
            }

        if (hasOwn(fieldConfig, 'visible')) {
            var containers = getFieldContainers(field, key);
            setElementsVisible(containers, isVisible(fieldConfig.visible));
        }
        });
    }

    function applyMappedFields(prefix, mapping) {
        Object.keys(mapping).forEach(function (configKey) {
            if (!hasOwn(config, configKey)) {
                return;
            }

            applyFieldConfig(prefix, mapping[configKey], config[configKey]);
        });
    }

    function applyOrderNotes() {
        if (!hasOwn(config, 'order-notes') || typeof config['order-notes'] !== 'object') {
            return;
        }

        var notesConfig = config['order-notes'];
        var notesVisible = hasOwn(notesConfig, 'visible') ? isVisible(notesConfig.visible) : true;
        var fields = toArray(
            document.querySelectorAll(
                '#order_comments, #order-comments, textarea[name="order_comments"], textarea[name="order-notes"]'
            )
        );

        fields.forEach(function (field) {
            if (hasOwn(notesConfig, 'placeholder') && notesConfig.placeholder !== undefined && notesConfig.placeholder !== null) {
                if (isBlocksElement(field)) {
                    field.removeAttribute('placeholder');
                } else {
                    field.setAttribute('placeholder', notesConfig.placeholder);
                }
            }

            if (hasOwn(notesConfig, 'required')) {
                var isRequired = !!notesConfig.required;
                field.required = isRequired;
                if (isRequired) {
                    field.setAttribute('aria-required', 'true');
                } else {
                    field.removeAttribute('aria-required');
                }
            }

            if (hasOwn(notesConfig, 'label')) {
                setLabelText(field, notesConfig.label, hasOwn(notesConfig, 'required') ? !!notesConfig.required : null);
            }

            if (hasOwn(notesConfig, 'visible')) {
                setElementsVisible(getFieldContainers(field, 'order_notes'), notesVisible);
            }
        });

        if (hasOwn(notesConfig, 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '.wc-block-checkout__order-notes, .wp-block-woocommerce-checkout-order-note-block'
                )),
                notesVisible
            );
        }
    }

    function applyPlaceOrderButton() {
        if (!hasOwn(config, 'place-order') || typeof config['place-order'] !== 'object') {
            return;
        }

        var placeOrderConfig = config['place-order'];
        var buttons = toArray(
            document.querySelectorAll(
                '#place_order, .wc-block-components-checkout-place-order-button'
            )
        );

        buttons.forEach(function (button) {
            if (hasOwn(placeOrderConfig, 'text') && placeOrderConfig.text !== undefined && placeOrderConfig.text !== null) {
                button.textContent = placeOrderConfig.text;
            }

            if (hasOwn(placeOrderConfig, 'visible')) {
                setElementsVisible([button], isVisible(placeOrderConfig.visible));
            }
        });
    }

    function applyCouponConfig() {
        if (!hasOwn(config, 'coupon-section') || typeof config['coupon-section'] !== 'object') {
            return;
        }

        var couponConfig = config['coupon-section'];

        if (hasOwn(couponConfig, 'placeholder')) {
            toArray(document.querySelectorAll('[name="coupon_code"], .wc-block-components-totals-coupon input'))
                .forEach(function (input) {
                    if (!isBlocksElement(input)) {
                        input.setAttribute('placeholder', couponConfig.placeholder);
                    }
                });
        }

        if (hasOwn(couponConfig, 'button')) {
            toArray(document.querySelectorAll('[name="apply_coupon"], .wc-block-components-totals-coupon__button'))
                .forEach(function (button) {
                    button.textContent = couponConfig.button;
                });
        }

        if (hasOwn(couponConfig, 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '.woocommerce-form-coupon-toggle, .checkout_coupon, .wc-block-components-totals-coupon, ' +
                    '.wc-block-components-totals-coupon-link, .wc-block-components-checkout-step--coupon, ' +
                    '.e-coupon-box, .e-woocommerce-coupon-nudge, .e-coupon-anchor, ' +
                    '.wp-block-woocommerce-checkout-order-summary-coupon-form-block'
                )),
                isVisible(couponConfig.visible)
            );
        }
    }

    function applyTextAndVisibility(configKey, textSelectors, visibilitySelectors) {
        if (!hasOwn(config, configKey) || typeof config[configKey] !== 'object') {
            return;
        }

        var conf = config[configKey];

        if (hasOwn(conf, 'text') && conf.text !== undefined && conf.text !== null && textSelectors) {
            toArray(document.querySelectorAll(textSelectors)).forEach(function (el) {
                el.textContent = conf.text;
            });
        }

        if (hasOwn(conf, 'visible') && visibilitySelectors) {
            setElementsVisible(
                toArray(document.querySelectorAll(visibilitySelectors)),
                isVisible(conf.visible)
            );
        }
    }

    function applyCheckoutStructureConfig() {
        applyTextAndVisibility(
            'billing-title',
            '.woocommerce-billing-fields > h3, .woocommerce-billing-fields h3, ' +
            '.wc-block-checkout__billing-fields .wc-block-components-checkout-step__title, ' +
            '.wp-block-woocommerce-checkout-billing-address-block .wc-block-components-checkout-step__title',
            '.woocommerce-billing-fields > h3, .woocommerce-billing-fields h3, ' +
            '.wc-block-checkout__billing-fields .wc-block-components-checkout-step__heading, ' +
            '.wc-block-checkout__billing-fields .wc-block-components-checkout-step__description, ' +
            '.wp-block-woocommerce-checkout-billing-address-block .wc-block-components-checkout-step__heading, ' +
            '.wp-block-woocommerce-checkout-billing-address-block .wc-block-components-checkout-step__description'
        );

        if (hasOwn(config, 'email') && typeof config.email === 'object' && hasOwn(config.email, 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '.wc-block-checkout__contact-fields .wc-block-components-checkout-step__heading, ' +
                    '.wc-block-checkout__contact-fields .wc-block-components-checkout-step__description, ' +
                    '.wp-block-woocommerce-checkout-contact-information-block .wc-block-components-checkout-step__heading, ' +
                    '.wp-block-woocommerce-checkout-contact-information-block .wc-block-components-checkout-step__description'
                )),
                isVisible(config.email.visible)
            );
        }

        applyTextAndVisibility(
            'additional-title',
            '.woocommerce-additional-fields > h3, .woocommerce-additional-fields h3',
            '.woocommerce-additional-fields > h3, .woocommerce-additional-fields h3'
        );

        applyTextAndVisibility(
            'order-review-title',
            '#order_review_heading, .e-checkout__order_review > h3, .et_pb_wc_checkout_order_details #order_review_heading',
            '#order_review_heading, .e-checkout__order_review > h3, .et_pb_wc_checkout_order_details #order_review_heading'
        );

        applyTextAndVisibility(
            'product-header',
            '.shop_table.woocommerce-checkout-review-order-table thead th.product-name',
            '.shop_table.woocommerce-checkout-review-order-table thead th.product-name'
        );

        applyTextAndVisibility(
            'subtotal-header',
            '.shop_table.woocommerce-checkout-review-order-table thead th.product-total',
            '.shop_table.woocommerce-checkout-review-order-table thead th.product-total'
        );

        if (hasOwn(config, 'order-summary') && typeof config['order-summary'] === 'object' && hasOwn(config['order-summary'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '.shop_table.woocommerce-checkout-review-order-table, #order_review, ' +
                    '.e-checkout__order_review, .e-checkout__order_review-2, ' +
                    '.wc-block-components-order-summary, .wc-block-checkout__sidebar, ' +
                    '.wp-block-woocommerce-checkout-order-summary-block, ' +
                    '.wp-block-woocommerce-checkout-totals-block, ' +
                    '.wc-block-checkout__totals, .wc-block-components-checkout-order-summary'
                )),
                isVisible(config['order-summary'].visible)
            );
        }

        if (hasOwn(config, 'order-item') && typeof config['order-item'] === 'object' && hasOwn(config['order-item'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tr.cart_item td.product-name')),
                isVisible(config['order-item'].visible)
            );
        }

        if (hasOwn(config, 'order-item-price') && typeof config['order-item-price'] === 'object' && hasOwn(config['order-item-price'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tr.cart_item td.product-total')),
                isVisible(config['order-item-price'].visible)
            );
        }

        if (hasOwn(config, 'subtotal2') && typeof config['subtotal2'] === 'object' && hasOwn(config['subtotal2'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tfoot tr.cart-subtotal th')),
                isVisible(config['subtotal2'].visible)
            );
        }

        if (hasOwn(config, 'subtotal-price') && typeof config['subtotal-price'] === 'object' && hasOwn(config['subtotal-price'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tfoot tr.cart-subtotal td')),
                isVisible(config['subtotal-price'].visible)
            );
        }

        applyTextAndVisibility(
            'shipping',
            '.shop_table tfoot tr.shipping th',
            '.shop_table tfoot tr.shipping th'
        );

        if (hasOwn(config, 'shipping-price') && typeof config['shipping-price'] === 'object' && hasOwn(config['shipping-price'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tfoot tr.shipping td')),
                isVisible(config['shipping-price'].visible)
            );
        }

        applyTextAndVisibility(
            'total-header',
            '.shop_table tfoot tr.order-total th, .shop_table tfoot tr:last-child th',
            '.shop_table tfoot tr.order-total th, .shop_table tfoot tr:last-child th'
        );

        if (hasOwn(config, 'total-price') && typeof config['total-price'] === 'object' && hasOwn(config['total-price'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll('.shop_table tfoot tr.order-total td, .shop_table tfoot tr:last-child td')),
                isVisible(config['total-price'].visible)
            );
        }
    }

    function applySectionVisibility() {
        if (hasOwn(config, 'payment-methods') && typeof config['payment-methods'] === 'object' && hasOwn(config['payment-methods'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '#payment, #payment ul.wc_payment_methods, .wc_payment_methods, ' +
                    '.e-checkout__order_review-2, .woocommerce-checkout-payment, ' +
                    '.wc-block-components-checkout-step--payment, ' +
                    '.wc-block-components-checkout-step--payment-methods, ' +
                    '.wc-block-checkout__payment-method, .wc-block-checkout__payment-methods, ' +
                    '.wc-block-components-payment-methods, .wc-block-components-checkout-payment-methods, ' +
                    '.wp-block-woocommerce-checkout-payment-block'
                )),
                isVisible(config['payment-methods'].visible)
            );
        }

        if (hasOwn(config, 'privacy-policy') && typeof config['privacy-policy'] === 'object' && hasOwn(config['privacy-policy'], 'visible')) {
            setElementsVisible(
                toArray(document.querySelectorAll(
                    '.woocommerce-terms-and-conditions-wrapper, .wc-block-checkout__terms, .wc-block-components-checkout-place-order-button__terms'
                )),
                isVisible(config['privacy-policy'].visible)
            );
        }
    }

    function applyAll() {
        applyMappedFields('billing', billingMap);
        applyMappedFields('shipping', shippingMap);
        applyOrderNotes();
        applyPlaceOrderButton();
        applyCouponConfig();
        applyCheckoutStructureConfig();
        applySectionVisibility();
    }

    var scheduled = false;
    function scheduleApply() {
        if (scheduled) {
            return;
        }
        scheduled = true;
        window.requestAnimationFrame(function () {
            scheduled = false;
            applyAll();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleApply);
    } else {
        scheduleApply();
    }

    var body = document.body;
    if (body) {
        ['updated_checkout', 'wc_fragments_loaded', 'wc_fragments_refreshed'].forEach(function (eventName) {
            body.addEventListener(eventName, scheduleApply);
        });
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            ['updated_checkout', 'wc_fragments_loaded', 'wc_fragments_refreshed'].forEach(function (eventName) {
                document.body.addEventListener(eventName, scheduleApply);
            });
        });
    }

    var observer = new MutationObserver(scheduleApply);
    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });
})();
