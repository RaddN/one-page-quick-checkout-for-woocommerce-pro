(function ($) {
    'use strict';

    var params = window.onepaqucpro_wc_cart_params || {};
    var checkoutRootSelector = '.wp-block-woocommerce-checkout, .wc-block-checkout, .wc-block-components-checkout';
    var summaryItemSelector = '.wc-block-components-order-summary .wc-block-components-order-summary-item';
    var renderQueued = false;
    var dataSubscribed = false;
    var domObserverStarted = false;

    function isEnabled(value, defaultValue) {
        if (value === undefined || value === null || value === '') {
            return defaultValue;
        }

        if (typeof value === 'boolean') {
            return value;
        }

        if (typeof value === 'number') {
            return value === 1;
        }

        if (typeof value === 'string') {
            return value === '1' || value.toLowerCase() === 'true';
        }

        return defaultValue;
    }

    var options = {
        quantityControl: isEnabled(params.blocks_quantity_control, true),
        removeProduct: isEnabled(params.blocks_remove_product, true),
        linkProduct: isEnabled(params.blocks_link_product, false),
        removeLabel: params.i18n_remove_item || 'Remove this item',
        decreaseLabel: params.i18n_decrease_quantity || 'Decrease quantity',
        increaseLabel: params.i18n_increase_quantity || 'Increase quantity'
    };

    function hasCheckoutBlocks() {
        return !!document.querySelector(checkoutRootSelector);
    }

    function getCartStoreKey() {
        if (
            window.wc &&
            window.wc.wcBlocksData &&
            window.wc.wcBlocksData.CART_STORE_KEY
        ) {
            return window.wc.wcBlocksData.CART_STORE_KEY;
        }

        return 'wc/store/cart';
    }

    function getCartSelector() {
        if (!window.wp || !window.wp.data || typeof window.wp.data.select !== 'function') {
            return null;
        }

        return window.wp.data.select(getCartStoreKey());
    }

    function getCartDispatch() {
        if (!window.wp || !window.wp.data || typeof window.wp.data.dispatch !== 'function') {
            return null;
        }

        return window.wp.data.dispatch(getCartStoreKey());
    }

    function getCartItems() {
        var selector = getCartSelector();

        if (!selector || typeof selector.getCartData !== 'function') {
            return [];
        }

        var cartData = selector.getCartData();

        if (!cartData || !Array.isArray(cartData.items)) {
            return [];
        }

        return cartData.items;
    }

    function parseNumber(value, fallback) {
        var parsed = parseInt(value, 10);

        if (!isFinite(parsed)) {
            return fallback;
        }

        return parsed;
    }

    function escapeAttr(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function triggerLegacyCartRefresh(eventType) {
        if (typeof window.jQuery === 'function' && document.body) {
            if (eventType === 'remove') {
                window.jQuery(document.body).trigger('removed_from_cart', [{}, '']);
            } else {
                window.jQuery(document.body).trigger('added_to_cart', [{}, '', null]);
            }
        }

        if (typeof window.updateCartContent === 'function') {
            window.updateCartContent(false);
        }
    }

    function triggerBlocksCartRefresh() {
        var dispatch = getCartDispatch();

        if (dispatch && typeof dispatch.invalidateResolutionForStore === 'function') {
            dispatch.invalidateResolutionForStore();
        } else if (dispatch && typeof dispatch.invalidateResolution === 'function') {
            dispatch.invalidateResolution('getCartData', []);
            dispatch.invalidateResolution('getCartTotals', []);
        }

        if (typeof window.CustomEvent === 'function') {
            window.dispatchEvent(
                new CustomEvent('wc-blocks_store_sync_required', {
                    detail: { type: 'from_@wordpress/data' }
                })
            );

            if (document.body) {
                document.body.dispatchEvent(
                    new CustomEvent('wc-blocks_removed_from_cart', {
                        bubbles: true
                    })
                );
            }
        }
    }

    function ajaxUpdateQuantity(itemKey, nextQuantity) {
        return $.ajax({
            type: 'POST',
            url: params.ajax_url || '',
            data: {
                action: 'onepaqucpro_update_cart_item_quantity',
                cart_item_key: itemKey,
                quantity: nextQuantity,
                nonce: params.update_cart_item_quantity
            }
        });
    }

    function ajaxRemoveItem(itemKey) {
        return $.ajax({
            type: 'POST',
            url: params.ajax_url || '',
            data: {
                action: 'onepaqucpro_remove_cart_item',
                cart_item_key: itemKey,
                nonce: params.remove_cart_item
            }
        });
    }

    function setRowBusy(row, isBusy) {
        if (!row) {
            return;
        }

        if (isBusy) {
            row.classList.add('onepaquc-blocks-item-updating');
        } else {
            row.classList.remove('onepaquc-blocks-item-updating');
        }

        var controls = row.querySelectorAll('.onepaquc-blocks-controls button, .onepaquc-blocks-controls input');
        var i = 0;

        for (i = 0; i < controls.length; i++) {
            controls[i].disabled = !!isBusy;
        }
    }

    function onPromiseFinished(promise, callback) {
        if (typeof callback !== 'function') {
            return;
        }

        if (!promise) {
            callback();
            return;
        }

        if (typeof promise.finally === 'function') {
            promise.finally(callback);
            return;
        }

        if (typeof promise.then === 'function') {
            promise.then(callback).catch(callback);
            return;
        }

        callback();
    }

    function updateQuantity(itemKey, nextQuantity, row) {
        var dispatch = getCartDispatch();
        var quantity = parseNumber(nextQuantity, 1);
        var promise = null;

        if (!itemKey || quantity < 1) {
            return;
        }

        setRowBusy(row, true);

        if (dispatch && typeof dispatch.changeCartItemQuantity === 'function') {
            promise = dispatch.changeCartItemQuantity(itemKey, quantity);
        } else {
            promise = ajaxUpdateQuantity(itemKey, quantity);
        }

        onPromiseFinished(promise, function () {
            setRowBusy(row, false);
            triggerLegacyCartRefresh('quantity');
            triggerBlocksCartRefresh();
            queueRender();
        });
    }

    function removeItem(itemKey, row) {
        var dispatch = getCartDispatch();
        var promise = null;

        if (!itemKey) {
            return;
        }

        setRowBusy(row, true);

        if (dispatch && typeof dispatch.removeItemFromCart === 'function') {
            promise = dispatch.removeItemFromCart(itemKey);
        } else {
            promise = ajaxRemoveItem(itemKey);
        }

        onPromiseFinished(promise, function () {
            setRowBusy(row, false);
            triggerLegacyCartRefresh('remove');
            triggerBlocksCartRefresh();
            queueRender();
        });
    }

    function ensureProductNameLink(row, cartItem) {
        if (!options.linkProduct || !row || !cartItem || !cartItem.permalink) {
            return;
        }

        var nameNode = row.querySelector('.wc-block-components-product-name');
        if (!nameNode) {
            return;
        }

        if (nameNode.tagName && nameNode.tagName.toLowerCase() === 'a') {
            nameNode.href = cartItem.permalink;
            nameNode.classList.add('onepaquc-blocks-product-link');
            return;
        }

        var currentText = nameNode.textContent || cartItem.name || '';
        var anchor = document.createElement('a');
        anchor.className = 'onepaquc-blocks-product-link';
        anchor.href = cartItem.permalink;
        anchor.textContent = currentText;

        nameNode.innerHTML = '';
        nameNode.appendChild(anchor);
    }

    function buildControlsMarkup(cartItem) {
        var limits = cartItem.quantity_limits || {};
        var min = parseNumber(limits.minimum, 1);
        var max = parseNumber(limits.maximum, 9999);
        var step = parseNumber(limits.multiple_of, 1);
        var quantity = parseNumber(cartItem.quantity, min);
        var canEditQuantity = options.quantityControl && limits.editable !== false && cartItem.sold_individually !== true;
        var showRemove = options.removeProduct;
        var html = '';

        if (!canEditQuantity && !showRemove) {
            return '';
        }

        if (quantity < min) {
            quantity = min;
        }

        if (max > 0 && quantity > max) {
            quantity = max;
        }

        html += '<div class="onepaquc-blocks-controls">';

        if (canEditQuantity) {
            html += '<div class="onepaquc-blocks-qty">';
            html += '<button type="button" class="onepaquc-blocks-qty-minus" aria-label="' + escapeAttr(options.decreaseLabel) + '">-</button>';
            html += '<input type="number" class="onepaquc-blocks-qty-input" min="' + min + '" max="' + max + '" step="' + step + '" value="' + quantity + '">';
            html += '<button type="button" class="onepaquc-blocks-qty-plus" aria-label="' + escapeAttr(options.increaseLabel) + '">+</button>';
            html += '</div>';
        }

        if (showRemove) {
            html += '<button type="button" class="onepaquc-blocks-remove" aria-label="' + escapeAttr(options.removeLabel) + '">&times;</button>';
        }

        html += '</div>';

        return html;
    }

    function ensureControls(row, cartItem) {
        if (!row || !cartItem) {
            return;
        }

        row.setAttribute('data-onepaquc-cart-item-key', cartItem.key || '');

        ensureProductNameLink(row, cartItem);

        var markup = buildControlsMarkup(cartItem);
        var existingControls = row.querySelector('.onepaquc-blocks-controls');

        if (!markup) {
            if (existingControls) {
                existingControls.parentNode.removeChild(existingControls);
            }
            return;
        }

        var target =
            row.querySelector('.wc-block-components-order-summary-item__total-price') ||
            row.querySelector('.wc-block-components-order-summary-item__description') ||
            row;

        if (existingControls) {
            existingControls.outerHTML = markup;
            return;
        }

        target.insertAdjacentHTML('beforeend', markup);
    }

    function render() {
        renderQueued = false;

        if (!hasCheckoutBlocks()) {
            return;
        }

        var rows = document.querySelectorAll(summaryItemSelector);
        var cartItems = getCartItems();
        var i = 0;

        if (!rows.length || !cartItems.length) {
            return;
        }

        for (i = 0; i < rows.length; i++) {
            if (!cartItems[i]) {
                continue;
            }

            ensureControls(rows[i], cartItems[i]);
        }
    }

    function queueRender() {
        if (renderQueued) {
            return;
        }

        renderQueued = true;

        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(render);
            return;
        }

        setTimeout(render, 20);
    }

    function nodeHasSummaryItems(node) {
        if (!node || node.nodeType !== 1) {
            return false;
        }

        if (node.matches && node.matches(summaryItemSelector)) {
            return true;
        }

        if (node.querySelector && node.querySelector(summaryItemSelector)) {
            return true;
        }

        return false;
    }

    function bindControlsEvents() {
        $(document).on('click', '.onepaquc-blocks-qty-minus', function (event) {
            event.preventDefault();

            var row = this.closest('.wc-block-components-order-summary-item');
            var input = row ? row.querySelector('.onepaquc-blocks-qty-input') : null;
            var itemKey = row ? row.getAttribute('data-onepaquc-cart-item-key') : '';
            var min = input ? parseNumber(input.getAttribute('min'), 1) : 1;
            var step = input ? parseNumber(input.getAttribute('step'), 1) : 1;
            var current = input ? parseNumber(input.value, min) : min;
            var next = current - step;

            if (next < min) {
                next = min;
            }

            if (input) {
                input.value = next;
            }

            updateQuantity(itemKey, next, row);
        });

        $(document).on('click', '.onepaquc-blocks-qty-plus', function (event) {
            event.preventDefault();

            var row = this.closest('.wc-block-components-order-summary-item');
            var input = row ? row.querySelector('.onepaquc-blocks-qty-input') : null;
            var itemKey = row ? row.getAttribute('data-onepaquc-cart-item-key') : '';
            var min = input ? parseNumber(input.getAttribute('min'), 1) : 1;
            var max = input ? parseNumber(input.getAttribute('max'), 9999) : 9999;
            var step = input ? parseNumber(input.getAttribute('step'), 1) : 1;
            var current = input ? parseNumber(input.value, min) : min;
            var next = current + step;

            if (max > 0 && next > max) {
                next = max;
            }

            if (input) {
                input.value = next;
            }

            updateQuantity(itemKey, next, row);
        });

        $(document).on('change', '.onepaquc-blocks-qty-input', function () {
            var row = this.closest('.wc-block-components-order-summary-item');
            var itemKey = row ? row.getAttribute('data-onepaquc-cart-item-key') : '';
            var min = parseNumber(this.getAttribute('min'), 1);
            var max = parseNumber(this.getAttribute('max'), 9999);
            var next = parseNumber(this.value, min);

            if (next < min) {
                next = min;
            }

            if (max > 0 && next > max) {
                next = max;
            }

            this.value = next;
            updateQuantity(itemKey, next, row);
        });

        $(document).on('click', '.onepaquc-blocks-remove', function (event) {
            event.preventDefault();

            var row = this.closest('.wc-block-components-order-summary-item');
            var itemKey = row ? row.getAttribute('data-onepaquc-cart-item-key') : '';

            removeItem(itemKey, row);
        });
    }

    function startDataSubscription() {
        if (dataSubscribed || !window.wp || !window.wp.data || typeof window.wp.data.subscribe !== 'function') {
            return;
        }

        var lastSnapshot = '';

        window.wp.data.subscribe(function () {
            if (!hasCheckoutBlocks()) {
                return;
            }

            var items = getCartItems();
            var i = 0;
            var snapshot = '';

            for (i = 0; i < items.length; i++) {
                snapshot += String(items[i].key || '') + ':' + String(items[i].quantity || 0) + '|';
            }

            if (snapshot === lastSnapshot) {
                return;
            }

            lastSnapshot = snapshot;
            queueRender();
        });

        dataSubscribed = true;
    }

    function startDomObserver() {
        if (domObserverStarted || typeof MutationObserver === 'undefined' || !document.body) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            if (!hasCheckoutBlocks()) {
                return;
            }

            var shouldRender = false;
            var i = 0;
            var j = 0;

            for (i = 0; i < mutations.length; i++) {
                var mutation = mutations[i];

                for (j = 0; j < mutation.addedNodes.length; j++) {
                    if (nodeHasSummaryItems(mutation.addedNodes[j])) {
                        shouldRender = true;
                        break;
                    }
                }

                if (shouldRender) {
                    break;
                }

                for (j = 0; j < mutation.removedNodes.length; j++) {
                    if (nodeHasSummaryItems(mutation.removedNodes[j])) {
                        shouldRender = true;
                        break;
                    }
                }

                if (shouldRender) {
                    break;
                }
            }

            if (shouldRender) {
                queueRender();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        domObserverStarted = true;
    }

    $(function () {
        if (!hasCheckoutBlocks()) {
            return;
        }

        bindControlsEvents();
        startDataSubscription();
        startDomObserver();
        queueRender();
    });
})(jQuery);
