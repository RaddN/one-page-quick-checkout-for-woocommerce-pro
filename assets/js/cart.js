jQuery(document).ready(function ($) {

    (function () {
        function resetAllCheckoutButtons() {
            $('#checkout-button-drawer-link').prop('disabled', false);
            $('.loading').removeClass('loading').prop('disabled', false);
        }

        window.addEventListener('pageshow', function (e) {
            const fromBFCache = e.persisted ||
                (performance && performance.getEntriesByType &&
                    performance.getEntriesByType('navigation')[0] &&
                    performance.getEntriesByType('navigation')[0].type === 'back_forward');

            if (fromBFCache) resetAllCheckoutButtons();
        });
    })();

    let isUpdatingCart = false;
    $isonepagewidget = ($('.checkout-popup,#checkout-popup').length) ? $('.checkout-popup,#checkout-popup').data('isonepagewidget') : false;

    function hasInlineCheckoutFormOnPage() {
        const inlineClassicCheckout = $('form.checkout.woocommerce-checkout').filter(function () {
            return !$(this).closest('.checkout-popup').not('.onepagecheckoutwidget').length;
        }).length > 0;
        const inlineBlockCheckout = $('.wp-block-woocommerce-checkout, .wc-block-checkout, .wc-block-components-checkout').filter(function () {
            return !$(this).closest('.checkout-popup').not('.onepagecheckoutwidget').length;
        }).length > 0;

        return !!(
            inlineClassicCheckout ||
            inlineBlockCheckout ||
            $('.one-page-checkout-container form.checkout.woocommerce-checkout').length
        );
    }

    function isPopupCheckoutVisible() {
        return $('.checkout-popup:visible').not('.onepagecheckoutwidget').length > 0;
    }

    // Function to fetch and update cart contents
    function updateCartContent(isdrawer = true) {
        if (isUpdatingCart) return;
        isUpdatingCart = true;
        const drawerWasOpen = $('.cart-drawer.open').length > 0;
        var cartIcon = $('.rwc_cart-button').data('cart-icon');
        var productTitleTag = $('.rwc_cart-button').data('product_title_tag');
        var drawerPosition = $('.rwc_cart-button').data('drawer-position');

        $.ajax({
            url: onepaqucpro_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaqucpro_get_cart_content',
                cart_icon: cartIcon,
                product_title_tag: productTitleTag,
                drawer_position: drawerPosition,
                nonce: onepaqucpro_wc_cart_params.get_cart_content_none
            },
            success: function (response) {
                if (response.success) {
                    const popupCheckoutVisible = isPopupCheckoutVisible();
                    const shouldKeepDrawerOpen = !popupCheckoutVisible && (isdrawer || drawerWasOpen) && response.data.cart_count !== 0;
                    let cartHtml = response.data.cart_html;

                    if (shouldKeepDrawerOpen) {
                        cartHtml = cartHtml.replace(/class="cart-drawer([^"]*)"/, function (match, classNames) {
                            return /\bopen\b/.test(classNames) ? match : 'class="cart-drawer' + classNames + ' open"';
                        });
                    }

                    $('.rmenupro-cart').html(cartHtml);

                    if (popupCheckoutVisible) {
                        $('.cart-drawer').removeClass('open');
                        $('.overlay').show();
                        document.body.style.overflow = 'hidden';
                    } else if (shouldKeepDrawerOpen) {
                        $('.overlay').show();
                        document.body.style.overflow = 'hidden';
                    }

                    isUpdatingCart = false;
                }
            },
            error: function () {
                isUpdatingCart = false;
            }
        });
    }

    window.updateCartCount = function (isIncrement = true, Value = 1) {
        // Select the cart count element
        const cartCountElement = document.querySelector('span.cart-count');

        $(document.body).on('removed_from_cart', function () {
            isIncrement = false;
        });

        // Check if the element exists
        if (cartCountElement) {
            // Get the current count, parse it as an integer, and increase it by the increment value
            let currentCount = parseInt(cartCountElement.textContent, 10);
            if (isIncrement) {
                currentCount += Value;
            } else {
                currentCount -= Value;
            }

            // Update the cart count display
            cartCountElement.textContent = currentCount;
        } else {
            console.error('Cart count element not found.');
        }
    };

    // Event handler for adding/removing items from the cart
    $(document.body).on('added_to_cart removed_from_cart', function () {
        const cartDrawer = document.querySelector('.cart-drawer');
        const hasInlineCheckout = hasInlineCheckoutFormOnPage();
        const canOpenDrawer = !hasInlineCheckout;

        const refreshCheckout = function () {
            if (hasInlineCheckout) {
                $(document.body).trigger('update_checkout');
                return;
            }

            if (typeof window.onepaqucproRefreshPopupCheckout === 'function') {
                window.onepaqucproRefreshPopupCheckout();
            }
        };

        if (cartDrawer && cartDrawer.classList.contains('open')) {
            refreshCheckout();
        } else {
            debouncedUpdate(canOpenDrawer);
            refreshCheckout();
        }
    });

    function debouncedUpdate(showdrawer = true) {
        const isDrawerOpen = ($('.cart-drawer').length && $('.cart-drawer').hasClass('open')) ? true : false;
        if (hasInlineCheckoutFormOnPage() && !isDrawerOpen) {
            showdrawer = false;
        }

        if (!showdrawer || ($isonepagewidget && !isDrawerOpen)) {
            updateCartContent(false);
        } else {
            updateCartContent(true);
        }
    }

    window.updateCartContent = function (isdrawer = true) {
        updateCartContent(isdrawer);
    }

    // Handle quantity change
    $('.rmenupro-cart').on('change', '.item-quantity', function () {
        const $input = $(this);
        const cartItemKey = $(this).closest('.cart-item').find('.remove-item').data('cart-item-key');
        const quantity = $(this).val();
        const cartCountElement = document.querySelector('span.cart-count');

        // Add loading class (spinner)
        $input.prop('disabled', true).parent().addClass('loading-spinner');


        $.ajax({
            url: onepaqucpro_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaqucpro_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: quantity,
                nonce: onepaqucpro_wc_cart_params.update_cart_item_quantity
            },
            success: function (response) {
                if (response.success) {
                    debouncedUpdate(false);
                    // Trigger WC events
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                }
            },
            complete: function () {
                // Remove loading class (spinner)
                $input.prop('disabled', false).parent().removeClass('loading-spinner');
            }
        });
    });

    // Handle remove item
    $('.rmenupro-cart').on('click', '.remove-item', function (e) {
        e.preventDefault();
        const cartItemKey = $(this).data('cart-item-key');
        const cartItem = $(this).closest('.cart-item');
        cartItem.addClass("removing");
        cartItem.css('transition', 'opacity 0.5s ease'); // Optional: add transition for smooth effect
        cartItem.css('opacity', '0.5'); // Optional: fade out the item

        window.removecartitem(cartItemKey);
    });

    window.removecartitem = function (cartItemKey) {
        const removingItems = document.querySelectorAll('.removing');
        let cart_count = document.querySelector('span.cart-count');
        const selectedCountText = document.getElementById('selected-count-text');
        const removeSelectedButton = document.getElementById('remove-selected');

        $.ajax({
            url: onepaqucpro_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaqucpro_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: onepaqucpro_wc_cart_params.remove_cart_item,
            },
            success: function (response) {
                if (response.success) {
                    window.updateCartTotals(response.data);

                    removingItems.forEach(item => {
                        let currentCount = parseInt(cart_count.textContent, 10) || 0;
                        currentCount -= 1;
                        // Update the element with the new count
                        cart_count.textContent = currentCount;
                        if (currentCount === 0) {
                            window.closeCheckoutPopup();
                            cart_count.textContent = "0";
                        }
                        item.classList.add('fade-out'); // Start fade-out animation
                        setTimeout(() => {
                            item.remove(); // Remove item after animation
                        }, 500); // Match timeout with CSS transition duration
                    });

                    // Update checkout totals
                    $(document.body).trigger('update_checkout');
                    selectedCountText.textContent = `0 selected`;
                    removeSelectedButton.style.display = 'none';

                    // Trigger WooCommerce hook
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                }
            }
        });
    }
    // handle quantity change

    // Function to update quantity
    function updateQuantity(cartItemKey, qty) {
        if (!cartItemKey) {
            return;
        }

        var $thisButton = $(this);

        // Block the checkout while updating
        $('.woocommerce-checkout-review-order-table').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        // Update via AJAX
        $.ajax({
            type: 'POST',
            url: onepaqucpro_wc_cart_params.ajax_url,
            data: {
                action: 'onepaqucpro_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: qty,
                nonce: onepaqucpro_wc_cart_params.update_cart_item_quantity
            },
            success: function (response) {
                // Trigger WC events
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisButton]);
                $(document.body).trigger('update_checkout');
                debouncedUpdate(false);
            },
            complete: function () {
                $('.woocommerce-checkout-review-order-table').unblock();
            }
        });
    }

    // Handle the plus button click
    $(document).on('click', '.checkout-qty-plus', function () {
        var input = $(this).prev('.checkout-qty-input');
        var val = parseFloat(input.val());
        var max = parseFloat(input.attr('max'));
        var step = parseFloat(input.attr('step')) || 1;

        if (max && (max <= val)) {
            input.val(max);
        } else {
            input.val(val + step);
        }

        updateQuantity($(this).data('cart-item'), val + step);
    });

    // Handle the minus button click
    $(document).on('click', '.checkout-qty-minus', function () {
        var input = $(this).next('.checkout-qty-input');
        var val = parseFloat(input.val());
        var min = parseFloat(input.attr('min')) || 1;
        var step = parseFloat(input.attr('step')) || 1;

        if (min && (min >= val)) {
            input.val(min);
        } else if (val > 0) {
            input.val(val - step);
        }

        updateQuantity($(this).data('cart-item'), Math.max(min, val - step));
    });

    // Handle direct input changes
    $(document).on('change', '.checkout-qty-input', function () {
        var val = parseFloat($(this).val());
        var min = parseFloat($(this).attr('min')) || 1;
        var cartItemKey = $(this).data('cart-item') || $(this).siblings('.checkout-qty-btn').first().data('cart-item');

        if (val < min) {
            $(this).val(min);
            val = min;
        }

        updateQuantity(cartItemKey, val);
    });

    // Handle click on remove item button
    $(document).on('click', '.remove-item-checkout', function (e) {
        e.preventDefault();
        var cartItemKey = $(this).data('cart-item');
        var $thisButton = $(this);

        $(this).closest('.cart_item').css('transition', 'opacity 0.5s ease'); // Optional: add transition for smooth effect
        $(this).closest('.cart_item').css('opacity', '0.5'); // Optional: fade out the item

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'onepaqucpro_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: onepaqucpro_wc_cart_params.remove_cart_item
            },
            success: function (response) {
                // Get product IDs from data attribute
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisButton]);
                var product_ids = $('.one-page-checkout-product-list').data('product-ids');

                // Refresh the list of products
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        action: 'onepaqucpro_refresh_checkout_product_list',
                        product_ids: product_ids,
                        nonce: onepaqucpro_wc_cart_params.onepaqucpro_refresh_checkout_product_list
                    },
                    success: function (html) {
                        $('ul.one-page-checkout-product-list').html(html);

                        // Update checkbox states based on cart contents
                        $('.one-page-checkout-product-item').each(function () {
                            var productId = $(this).data('product-id');
                            var inCart = $(this).data('cart-item-key') !== '';
                            $(this).find('.one-page-checkout-product-checkbox').prop('checked', inCart);
                        });
                    }
                });

                // Update WooCommerce fragments
                if (response.fragments) {
                    $.each(response.fragments, function (key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Update checkout totals
                $(document.body).trigger('update_checkout');
                debouncedUpdate(false);
            }
        });
    });

    function parseVariationEditorData($editor) {
        var $dataNode = $editor.find('.onepaqucpro-cart-variation-editor__data').first();
        var editorInfo = $dataNode.data('editor-info');

        if (typeof editorInfo === 'string') {
            try {
                editorInfo = JSON.parse(editorInfo);
            } catch (error) {
                editorInfo = null;
            }

            $dataNode.data('editor-info', editorInfo);
        }

        return editorInfo && typeof editorInfo === 'object' ? editorInfo : null;
    }

    function getVariationEditorAttrKeys(editorInfo) {
        if (editorInfo && $.isArray(editorInfo.attr_keys) && editorInfo.attr_keys.length) {
            return editorInfo.attr_keys.slice();
        }

        if (editorInfo && editorInfo.attributes) {
            return Object.keys(editorInfo.attributes);
        }

        return [];
    }

    function getVariationEditorSelection($editor) {
        var selection = {};

        $editor.find('.onepaqucpro-cart-variation-editor__select').each(function () {
            var attributeKey = $(this).data('variation-attr');
            var value = $(this).val();

            if (attributeKey && value) {
                selection[String(attributeKey)] = value;
            }
        });

        return selection;
    }

    function buildVariationEditorSignature(variationId, selection, attrKeys) {
        var orderedSelection = {};

        $.each(attrKeys || [], function (_, attrKey) {
            orderedSelection[attrKey] = selection && selection[attrKey] ? String(selection[attrKey]) : '';
        });

        return String(variationId || '') + '|' + JSON.stringify(orderedSelection);
    }

    function findMatchingVariationForEditor(editorInfo, selection) {
        var attrKeys = getVariationEditorAttrKeys(editorInfo);
        var variations = editorInfo && $.isArray(editorInfo.variations) ? editorInfo.variations : [];
        var i;
        var j;

        for (i = 0; i < variations.length; i++) {
            var variation = variations[i];
            var isMatch = true;

            for (j = 0; j < attrKeys.length; j++) {
                var attrKey = attrKeys[j];

                if (!selection[attrKey] || !variation.attrs || variation.attrs[attrKey] !== selection[attrKey]) {
                    isMatch = false;
                    break;
                }
            }

            if (isMatch) {
                return variation;
            }
        }

        return null;
    }

    function setVariationEditorFeedback($editor, message, type) {
        var $feedback = $editor.find('.onepaqucpro-cart-variation-editor__feedback');

        $feedback.removeClass('is-error is-success');

        if (!message) {
            $feedback.text('');
            return;
        }

        $feedback.text(message);

        if (type === 'success') {
            $feedback.addClass('is-success');
        } else {
            $feedback.addClass('is-error');
        }
    }

    function setVariationEditorLoading($editor, isLoading) {
        $editor.toggleClass('is-loading', !!isLoading);
        $editor.find('.onepaqucpro-cart-variation-editor__toggle, .onepaqucpro-cart-variation-editor__select, .onepaqucpro-cart-variation-editor__apply, .onepaqucpro-cart-variation-editor__cancel')
            .prop('disabled', !!isLoading);
    }

    function syncVariationEditor($editor) {
        var editorInfo = parseVariationEditorData($editor);
        var attrKeys = getVariationEditorAttrKeys(editorInfo);
        var selection = getVariationEditorSelection($editor);
        var match = null;
        var hasMissingSelection = false;
        var originalSelection = editorInfo && editorInfo.selected_attributes ? editorInfo.selected_attributes : {};
        var originalSignature;
        var currentSignature = '';
        var hasChanged = false;
        var i;

        for (i = 0; i < attrKeys.length; i++) {
            if (!selection[attrKeys[i]]) {
                hasMissingSelection = true;
                break;
            }
        }

        if (!hasMissingSelection) {
            match = findMatchingVariationForEditor(editorInfo, selection);
        }

        originalSignature = buildVariationEditorSignature(
            editorInfo && editorInfo.selected_variation_id ? editorInfo.selected_variation_id : '',
            originalSelection,
            attrKeys
        );

        if (match) {
            currentSignature = buildVariationEditorSignature(match.id, selection, attrKeys);
            hasChanged = currentSignature !== originalSignature;
        }

        $editor.find('.onepaqucpro-cart-variation-editor__apply')
            .prop('disabled', !match || !hasChanged)
            .data('variation-id', match ? match.id : '');

        if (!hasMissingSelection && !match) {
            setVariationEditorFeedback($editor, onepaqucpro_wc_cart_params.i18n_select_variation_error || 'Please choose a valid variation before updating.', 'error');
        } else {
            setVariationEditorFeedback($editor, '', '');
        }

        return {
            editorInfo: editorInfo,
            attrKeys: attrKeys,
            selection: selection,
            match: match,
            hasChanged: hasChanged
        };
    }

    function resetVariationEditor($editor) {
        var editorInfo = parseVariationEditorData($editor);
        var selectedAttributes = editorInfo && editorInfo.selected_attributes ? editorInfo.selected_attributes : {};

        $editor.find('.onepaqucpro-cart-variation-editor__select').each(function () {
            var attributeKey = $(this).data('variation-attr');
            $(this).val(attributeKey && selectedAttributes[attributeKey] ? selectedAttributes[attributeKey] : '');
        });

        syncVariationEditor($editor);
    }

    var variationPanelRepositionTimer = null;

    function clearVariationEditorPanelPosition($editor) {
        $editor.find('.onepaqucpro-cart-variation-editor__panel').css({
            position: '',
            left: '',
            top: '',
            right: '',
            bottom: '',
            width: '',
            maxWidth: '',
            maxHeight: '',
            marginTop: '',
            zIndex: '',
            overflowY: '',
            overflow: ''
        });
    }

    /**
     * When an ancestor has transform/filter/perspective, position:fixed is laid out
     * relative to that ancestor — not the viewport. Viewport coords must be converted.
     */
    function getFixedContainingBlockRect(fixedEl) {
        var node = fixedEl && fixedEl.parentElement;
        while (node && node !== document.documentElement) {
            var style = window.getComputedStyle(node);
            var transform = style.transform;
            var filter = style.filter;
            var perspective = style.perspective;
            if (
                (transform && transform !== 'none') ||
                (filter && filter !== 'none') ||
                (perspective && perspective !== 'none' && perspective !== '0px')
            ) {
                return node.getBoundingClientRect();
            }
            node = node.parentElement;
        }
        return null;
    }

    function positionVariationEditorPanel($editor) {
        var $panel = $editor.find('.onepaqucpro-cart-variation-editor__panel');
        var $toggle = $editor.find('.onepaqucpro-cart-variation-editor__toggle');
        var panelEl = $panel[0];

        if (!$panel.length || !$toggle.length || $panel.prop('hidden')) {
            return;
        }

        clearVariationEditorPanelPosition($editor);

        var margin = 8;
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var pw = Math.max($panel.outerWidth(), 260);
        var ph = $panel.outerHeight();
        var rect = $toggle[0].getBoundingClientRect();
        var blockRect = getFixedContainingBlockRect(panelEl);

        var spaceBelow = vh - rect.bottom - margin;
        var spaceAbove = rect.top - margin;
        var placeAbove = ph + margin > spaceBelow && spaceAbove > spaceBelow;

        var topV = placeAbove ? rect.top - ph - margin : rect.bottom + margin;
        var leftV = rect.right - pw;

        leftV = Math.max(margin, Math.min(leftV, vw - pw - margin));
        topV = Math.max(margin, Math.min(topV, vh - ph - margin));

        var maxH = placeAbove
            ? Math.min(380, Math.max(120, rect.top - margin * 2))
            : Math.min(380, Math.max(120, vh - rect.bottom - margin * 2));

        var leftPx;
        var topPx;

        if (blockRect) {
            leftPx = leftV - blockRect.left;
            topPx = topV - blockRect.top;
        } else {
            leftPx = leftV;
            topPx = topV;
        }

        $panel.css({
            position: 'fixed',
            left: leftPx + 'px',
            top: topPx + 'px',
            width: pw + 'px',
            maxHeight: maxH + 'px',
            marginTop: '0',
            overflowY: 'auto',
            zIndex: 10000000,
            boxSizing: 'border-box'
        });
    }

    function scheduleVariationEditorPanelPosition($editor) {
        if (variationPanelRepositionTimer) {
            clearTimeout(variationPanelRepositionTimer);
        }
        variationPanelRepositionTimer = setTimeout(function () {
            requestAnimationFrame(function () {
                positionVariationEditorPanel($editor);
            });
        }, 10);
    }

    function closeVariationEditor($editor) {
        $editor.removeClass('is-open');
        $editor.find('.onepaqucpro-cart-variation-editor__toggle').attr('aria-expanded', 'false');
        $editor.find('.onepaqucpro-cart-variation-editor__panel').prop('hidden', true);
        clearVariationEditorPanelPosition($editor);
    }

    function openVariationEditor($editor) {
        $editor.addClass('is-open');
        $editor.find('.onepaqucpro-cart-variation-editor__toggle').attr('aria-expanded', 'true');
        $editor.find('.onepaqucpro-cart-variation-editor__panel').prop('hidden', false);
        syncVariationEditor($editor);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                positionVariationEditorPanel($editor);
            });
        });
    }

    $(document).on('click', '.onepaqucpro-cart-variation-editor__toggle', function (event) {
        event.preventDefault();

        var $editor = $(this).closest('.onepaqucpro-cart-variation-editor');

        if ($editor.hasClass('is-open')) {
            closeVariationEditor($editor);
            return;
        }

        openVariationEditor($editor);
    });

    $(document).on('click.onepaqucproVarOutside', function (event) {
        var $target = $(event.target);
        $('.onepaqucpro-cart-variation-editor.is-open').each(function () {
            var $editor = $(this);
            var innerEditor = $target.closest('.onepaqucpro-cart-variation-editor')[0];
            if (innerEditor !== $editor[0]) {
                closeVariationEditor($editor);
            }
        });
    });

    $(document).on('change', '.onepaqucpro-cart-variation-editor__select', function () {
        var $editor = $(this).closest('.onepaqucpro-cart-variation-editor');
        syncVariationEditor($editor);
        if ($editor.hasClass('is-open')) {
            scheduleVariationEditorPanelPosition($editor);
        }
    });

    $(window).on('resize.onepaqucproVarPanel', function () {
        $('.onepaqucpro-cart-variation-editor.is-open').each(function () {
            scheduleVariationEditorPanelPosition($(this));
        });
    });

    if (!window._onepaqucproVarPanelScrollBound) {
        window._onepaqucproVarPanelScrollBound = true;
        document.addEventListener('scroll', function () {
            $('.onepaqucpro-cart-variation-editor.is-open').each(function () {
                scheduleVariationEditorPanelPosition($(this));
            });
        }, true);
    }

    $(document).on('click', '.onepaqucpro-cart-variation-editor__cancel', function (event) {
        event.preventDefault();

        var $editor = $(this).closest('.onepaqucpro-cart-variation-editor');
        resetVariationEditor($editor);
        closeVariationEditor($editor);
    });

    $(document).on('click', '.onepaqucpro-cart-variation-editor__apply', function (event) {
        event.preventDefault();

        var $editor = $(this).closest('.onepaqucpro-cart-variation-editor');
        var cartItemKey = $editor.data('cart-item-key') || '';
        var state = syncVariationEditor($editor);
        var isCartPageEditor = $editor.hasClass('onepaqucpro-cart-variation-editor--cart') && $('body').hasClass('woocommerce-cart');
        var isBlocksCartEditor = $editor.hasClass('onepaqucpro-cart-variation-editor--blocks-cart');
        var isBlocksCheckoutEditor = $editor.hasClass('onepaqucpro-cart-variation-editor--blocks-checkout');

        if (!cartItemKey || !state.match || !state.hasChanged) {
            setVariationEditorFeedback($editor, onepaqucpro_wc_cart_params.i18n_select_variation_error || 'Please choose a valid variation before updating.', 'error');
            return;
        }

        setVariationEditorLoading($editor, true);

        $.ajax({
            type: 'POST',
            url: onepaqucpro_wc_cart_params.ajax_url,
            data: {
                action: 'onepaqucpro_update_cart_item_variation',
                cart_item_key: cartItemKey,
                variation_id: state.match.id,
                variations: state.selection,
                nonce: onepaqucpro_wc_cart_params.update_cart_item_variation
            },
            success: function (response) {
                if (!response || !response.success) {
                    var errorMessage = response && response.data && response.data.message
                        ? response.data.message
                        : (response && response.message ? response.message : onepaqucpro_wc_cart_params.i18n_update_variation_error);

                    setVariationEditorFeedback($editor, errorMessage || 'Could not update the selected variation. Please try again.', 'error');
                    return;
                }

                if (isCartPageEditor) {
                    window.location.reload();
                    return;
                }

                closeVariationEditor($editor);

                if (isBlocksCartEditor || isBlocksCheckoutEditor) {
                    if (typeof window.onepaqucproBlocksRefreshCartAfterVariationChange === 'function') {
                        window.onepaqucproBlocksRefreshCartAfterVariationChange();
                    }
                    return;
                }

                debouncedUpdate(false);

                if (hasInlineCheckoutFormOnPage()) {
                    $(document.body).trigger('update_checkout');
                } else if (typeof window.onepaqucproRefreshPopupCheckout === 'function') {
                    window.onepaqucproRefreshPopupCheckout();
                }

                $(document.body).trigger('added_to_cart', [response.fragments || {}, response.cart_hash || '', $editor]);
                $(document.body).trigger('update_checkout');
            },
            error: function () {
                setVariationEditorFeedback(
                    $editor,
                    onepaqucpro_wc_cart_params.i18n_update_variation_error || 'Could not update the selected variation. Please try again.',
                    'error'
                );
            },
            complete: function () {
                setVariationEditorLoading($editor, false);
            }
        });
    });

    var $directbehave = onepaqucpro_wc_cart_params.direct_checkout_behave;
    var methodKey = $directbehave.rmenupro_wc_checkout_method;
    var archiveVariationModalState = null;

    function canUseArchiveVariationPopup() {
        return !!(window.onepaqucpro_wc_cart_params && onepaqucpro_wc_cart_params.premium_feature);
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function appendHiddenCartDrawerStyle() {
        const cartDrawer = $('.cart-drawer');

        if (!cartDrawer.length || $('#cart-drawer2-style').length) {
            return;
        }

        $('body').append(`
            <style id="cart-drawer2-style">
                .cart-drawer,.overlay {
                    opacity: 0 !important;
                    visibility: hidden !important;
                    display: none !important;
                }
                body{
                    overflow: auto !important;
                }
            </style>
        `);
    }

    function handleWooFragments(response) {
        if (!response.fragments) {
            return;
        }

        $.each(response.fragments, function (key, value) {
            $(key).replaceWith(value);
        });

        if (typeof sessionStorage !== 'undefined') {
            sessionStorage.setItem('wc_fragments', JSON.stringify(response.fragments));
            sessionStorage.setItem('wc_cart_hash', response.cart_hash);
        }
    }

    function getArchiveVariationContainer($button, $overrideContainer) {
        if ($overrideContainer && $overrideContainer.length) {
            return $overrideContainer.first();
        }

        return $button.closest('.product').find('.archive-variations-container').first();
    }

    function toTitleCaseFromSlug(value) {
        return String(value || '')
            .replace(/^attribute_/, '')
            .replace(/^pa_/, '')
            .replace(/[-_]+/g, ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function normalizeVariationMap(attrs) {
        var normalized = {};

        $.each(attrs || {}, function (key, value) {
            if (typeof value === 'undefined' || value === null || value === '') {
                return;
            }

            normalized[String(key).replace(/^attribute_/, '')] = value;
        });

        return normalized;
    }

    function parsePopupInfoData($element, attributeName) {
        if (!$element.length) {
            return null;
        }

        var parsedData = $element.data(attributeName);

        if (typeof parsedData === 'string') {
            try {
                parsedData = JSON.parse(parsedData);
            } catch (error) {
                parsedData = null;
            }
        }

        return parsedData && typeof parsedData === 'object' ? parsedData : null;
    }

    function getVariationPopupSourceData($product) {
        var popupData = parsePopupInfoData($product.find('.onepaqucpro-variation-popup-data[data-popup-info]').first(), 'popup-info');

        if (popupData) {
            return popupData;
        }

        return parsePopupInfoData($product.find('.rmenupro-product-data[data-product-info]').first(), 'product-info');
    }

    function normalizeVariationPopupData(rawData) {
        if (!rawData || typeof rawData !== 'object') {
            return null;
        }

        var normalizedAttributes = {};
        var attrKeys = [];

        $.each(rawData.attributes || {}, function (attrKey, attrInfo) {
            var normalizedKey = String(attrKey).replace(/^attribute_/, '');
            var options = [];
            var label = rawData.attribute_labels && rawData.attribute_labels[normalizedKey]
                ? rawData.attribute_labels[normalizedKey]
                : toTitleCaseFromSlug(normalizedKey);

            if ($.isArray(attrInfo)) {
                options = $.map(attrInfo, function (value) {
                    return {
                        slug: value,
                        label: toTitleCaseFromSlug(value)
                    };
                });
            } else if (attrInfo && typeof attrInfo === 'object' && $.isArray(attrInfo.options)) {
                options = attrInfo.options;
                label = attrInfo.label || label;
            }

            if (!options.length) {
                return;
            }

            normalizedAttributes[normalizedKey] = {
                label: label,
                options: options
            };
            attrKeys.push(normalizedKey);
        });

        var normalizedVariations = $.map(rawData.variations || [], function (variation) {
            var variationId = variation.id || variation.variation_id;
            var attrs = normalizeVariationMap(variation.attrs || variation.attributes || {});

            if (!variationId || !Object.keys(attrs).length) {
                return null;
            }

            return {
                id: String(variationId),
                attrs: attrs,
                priceHtml: variation.price_html || '',
                imageSrc: variation.image_src || ''
            };
        });

        if (!normalizedVariations.length || !attrKeys.length) {
            return null;
        }

        return {
            title: rawData.title || '',
            priceHtml: rawData.price_html || '',
            imageSrc: rawData.image_src || '',
            attributes: normalizedAttributes,
            attrKeys: rawData.attr_keys && rawData.attr_keys.length
                ? $.map(rawData.attr_keys, function (key) { return String(key).replace(/^attribute_/, ''); })
                : attrKeys,
            variations: normalizedVariations
        };
    }

    function buildFallbackArchiveVariationContainer($product) {
        var popupData = normalizeVariationPopupData(getVariationPopupSourceData($product));
        if (!popupData) {
            return $();
        }

        var $container = $('<div class="archive-variations-container onepaqucpro-variation-modal__generated" data-layout="separate"></div>');
        var $box = $('<div class="separate-attrs"></div>');

        $('<script type="application/json" class="var-map"></script>')
            .text(JSON.stringify(popupData.variations))
            .appendTo($box);

        $('<script type="application/json" class="attr-keys"></script>')
            .text(JSON.stringify(popupData.attrKeys))
            .appendTo($box);

        $.each(popupData.attrKeys, function (index, attrKey) {
            var attrInfo = popupData.attributes[attrKey];
            if (!attrInfo || !$.isArray(attrInfo.options) || !attrInfo.options.length) {
                return;
            }

            var $group = $('<div class="var-attr-group"></div>').attr('data-attr', attrKey);
            $('<span class="var-attr-title"></span>').text(attrInfo.label + ':').appendTo($group);

            var $options = $('<div class="var-attr-options"></div>');
            $.each(attrInfo.options, function (optionIndex, option) {
                $('<button type="button" class="var-attr-option"></button>')
                    .attr('data-attr', attrKey)
                    .attr('data-value', option.slug)
                    .text(option.label)
                    .appendTo($options);
            });

            $group.append($options).appendTo($box);
        });

        $container.append($box);
        $container.append('<input type="hidden" class="variation_id" value="">');

        return $container;
    }

    function getQuantityForAction($button, product_id) {
        var $form = $button.closest('form.cart');
        var $product = $button.closest('.product');
        var quantity = 1;

        if ($form.length > 0) {
            quantity = $form.find('input.qty').val();
        } else {
            var $qtyInput = $button.siblings('.rmenu-archive-quantity');

            if (!$qtyInput.length && $product.length) {
                $qtyInput = $product.find('.rmenu-archive-quantity').first();
            }

            if ($qtyInput.length > 0) {
                quantity = $qtyInput.val();
            } else {
                var $qtyWrapper = $('.rmenu-quantity-wrapper[data-product_id="' + product_id + '"]');
                var qtyByData = $button.data('quantity');

                if ($qtyWrapper.length > 0) {
                    var $qtyField = $qtyWrapper.find('.rmenu-archive-quantity');
                    if ($qtyField.length > 0) {
                        quantity = $qtyField.val();
                    }
                } else if (qtyByData) {
                    quantity = qtyByData;
                } else {
                    var $qtyById = $('#quantity_' + product_id);
                    if ($qtyById.length > 0) {
                        quantity = $qtyById.val();
                    }
                }
            }
        }

        if (!quantity || quantity < 1 || isNaN(quantity)) {
            quantity = 1;
        }

        return quantity;
    }

    function getSelectedArchiveVariations($archiveContainer) {
        var variations = {};

        if (!$archiveContainer.length) {
            return variations;
        }

        var selectedAttrs = $archiveContainer.find('.variation-button.selected').first().data('attrs');

        if (typeof selectedAttrs === 'string') {
            try {
                selectedAttrs = JSON.parse(selectedAttrs);
            } catch (error) {
                selectedAttrs = {};
            }
        }

        if (selectedAttrs && typeof selectedAttrs === 'object') {
            return $.extend({}, selectedAttrs);
        }

        $archiveContainer.find('.var-attr-group').each(function () {
            var attribute = $(this).data('attr') || $(this).find('button.var-attr-option.selected').data('attr');
            var value = $(this).find('button.var-attr-option.selected').data('value');

            if (attribute && value) {
                variations[attribute] = value;
            }
        });

        return variations;
    }

    function collectVariationState($button, product_id, $overrideContainer) {
        var $form = $button.closest('form.cart');
        var $product = $button.closest('.product');
        var $archiveContainer = getArchiveVariationContainer($button, $overrideContainer);
        var variationId = ($archiveContainer.length ? $archiveContainer.find('.variation_id').val() : '') ||
            $button.siblings('.archive-variations-container').find('.variation_id').val() ||
            $button.siblings('.variation_id').val() ||
            $product.find('.variation_id').first().val() ||
            $button.data('variation-id') || 0;
        var variations = {};
        var requiredAttributes = [];
        var isFormVariationContext = $form.length > 0 && $form.find('input[name="variation_id"]').length > 0 && !$overrideContainer;

        if (isFormVariationContext) {
            variationId = $form.find('input[name="variation_id"]').val() || variationId;
            $form.find('.variations select').each(function () {
                var attribute = $(this).attr('name');
                var value = $(this).val();

                if (attribute) {
                    requiredAttributes.push(attribute);
                }

                if (value) {
                    variations[attribute] = value;
                }
            });
        } else if ($archiveContainer.length) {
            variations = getSelectedArchiveVariations($archiveContainer);
            $archiveContainer.find('.var-attr-group').each(function () {
                var attrName = $(this).data('attr') || $(this).data('attribute') || $(this).find('button.var-attr-option').first().data('attr');

                if (attrName) {
                    requiredAttributes.push(attrName);
                }
            });
        }

        return {
            variationId: parseInt(variationId, 10) || 0,
            variations: variations,
            requiredAttributes: requiredAttributes,
            quantity: getQuantityForAction($button, product_id),
            $archiveContainer: $archiveContainer,
            isArchiveContext: $archiveContainer.length > 0
        };
    }

    function validateVariationSelection(product_type, selection) {
        if (product_type !== 'variable') {
            return {
                valid: true
            };
        }

        if (selection.variationId === 0) {
            return {
                valid: false,
                message: 'Please select all product options before adding this product to your cart.'
            };
        }

        var selectedCount = Object.keys(selection.variations).length;
        var requiredCount = selection.requiredAttributes.length;

        if (requiredCount > 0 && selectedCount < requiredCount) {
            return {
                valid: false,
                message: 'Please select all product options. You have selected ' + selectedCount + ' out of ' + requiredCount + ' required options.'
            };
        }

        var hasEmptyVariation = false;
        $.each(selection.variations, function (key, value) {
            if (!value || value === '' || value === 'undefined') {
                hasEmptyVariation = true;
                return false;
            }
        });

        if (hasEmptyVariation) {
            return {
                valid: false,
                message: 'Please complete all product option selections.'
            };
        }

        return {
            valid: true
        };
    }

    function setArchiveVariationModalFeedback(message, type) {
        if (!archiveVariationModalState || !archiveVariationModalState.$modal.length) {
            return;
        }

        var $feedback = archiveVariationModalState.$modal.find('.onepaqucpro-variation-modal__feedback');
        $feedback.removeClass('is-visible is-error is-success');

        if (!message) {
            $feedback.text('');
            return;
        }

        $feedback.text(message)
            .addClass('is-visible')
            .addClass(type === 'success' ? 'is-success' : 'is-error');
    }

    function setArchiveVariationModalLoading(isLoading) {
        if (!archiveVariationModalState || !archiveVariationModalState.$modal.length) {
            return;
        }

        archiveVariationModalState.$modal.find('.onepaqucpro-variation-modal__action')
            .toggleClass('is-loading', !!isLoading)
            .prop('disabled', !!isLoading);
    }

    function findArchiveVariationModalData(selection) {
        if (!archiveVariationModalState || !archiveVariationModalState.popupData || !archiveVariationModalState.popupData.variations) {
            return null;
        }

        var popupData = archiveVariationModalState.popupData;
        var variationId = selection && selection.variationId ? String(selection.variationId) : '';
        var selectedAttrs = normalizeVariationMap(selection && selection.variations ? selection.variations : {});

        if (variationId) {
            for (var i = 0; i < popupData.variations.length; i++) {
                if (popupData.variations[i].id === variationId) {
                    return popupData.variations[i];
                }
            }
        }

        if (!Object.keys(selectedAttrs).length) {
            return null;
        }

        for (var j = 0; j < popupData.variations.length; j++) {
            var variation = popupData.variations[j];
            var isMatch = true;

            for (var k = 0; k < popupData.attrKeys.length; k++) {
                var attrKey = popupData.attrKeys[k];

                if (!selectedAttrs[attrKey] || variation.attrs[attrKey] !== selectedAttrs[attrKey]) {
                    isMatch = false;
                    break;
                }
            }

            if (isMatch) {
                return variation;
            }
        }

        return null;
    }

    function updateArchiveVariationModalSummary(selection) {
        if (!archiveVariationModalState || !archiveVariationModalState.$modal.length) {
            return;
        }

        var state = archiveVariationModalState;
        var variationData = findArchiveVariationModalData(selection);
        var summary = $.extend({}, state.defaultSummary);

        if (variationData) {
            if (variationData.priceHtml) {
                summary.priceHtml = variationData.priceHtml;
            }

            if (variationData.imageSrc) {
                summary.imageSrc = variationData.imageSrc;
            }
        }

        var $hero = state.$modal.find('.onepaqucpro-variation-modal__hero');
        var $summary = state.$modal.find('.onepaqucpro-variation-modal__summary');
        var $price = $summary.find('.onepaqucpro-variation-modal__price');
        var $media = $hero.find('.onepaqucpro-variation-modal__media');

        if (summary.priceHtml) {
            if (!$price.length) {
                $price = $('<div class="onepaqucpro-variation-modal__price"></div>');
                $summary.find('.onepaqucpro-variation-modal__title').after($price);
            }

            $price.html(summary.priceHtml);
        } else {
            $price.remove();
        }

        if (summary.imageSrc) {
            if (!$media.length) {
                $media = $('<div class="onepaqucpro-variation-modal__media"></div>');
                $hero.prepend($media);
            }

            var $img = $media.find('img');
            if (!$img.length) {
                $img = $('<img>');
                $media.empty().append($img);
            }

            $img.attr('src', summary.imageSrc)
                .attr('alt', state.defaultSummary.imageAlt || state.defaultSummary.title || '');
        } else {
            $media.remove();
        }
    }

    function closeArchiveVariationPopup() {
        if (!archiveVariationModalState) {
            return;
        }

        var state = archiveVariationModalState;

        $(document).off('keydown.onepaqucproVariationModal');

        if (state.isSynthetic) {
            if (state.$container && state.$container.length) {
                state.$container.remove();
            }
        } else if (state.$container && state.$container.length && state.$placeholder && state.$placeholder.length) {
            state.$container.removeClass('onepaqucpro-variation-modal__moved-options');

            if (state.wasOverlay) {
                state.$container.addClass('overlay-variations');
            }

            if (state.wasBottomOffset) {
                state.$container.addClass('bottom-48');
            }

            state.$placeholder.replaceWith(state.$container);
        }

        if (state.$modal && state.$modal.length) {
            state.$modal.remove();
        }

        $('body').removeClass('onepaqucpro-variation-modal-open');
        archiveVariationModalState = null;
    }

    function openArchiveVariationPopup($button, message) {
        if (!canUseArchiveVariationPopup()) {
            return false;
        }

        var $product = $button.closest('.product');
        var $container = $product.find('.archive-variations-container').first();
        var popupData = normalizeVariationPopupData(getVariationPopupSourceData($product));
        var isSynthetic = false;

        if (!$product.length) {
            return false;
        }

        if (!$container.length) {
            $container = buildFallbackArchiveVariationContainer($product);
            isSynthetic = $container.length > 0;
        }

        if (!$container.length) {
            return false;
        }

        closeArchiveVariationPopup();

        var title = $.trim($button.data('title') || $product.find('.woocommerce-loop-product__title').first().text() || 'Choose product options');
        var shortDescription = $.trim(String(
            $button.attr('data-short-description') ||
            $button.data('shortDescription') ||
            $product.find('.woocommerce-product-details__short-description').first().text() ||
            ''
        ).replace(/\s+/g, ' '));
        var priceHtml = popupData && popupData.priceHtml ? popupData.priceHtml : ($product.find('.price').first().html() || '');
        var $image = $product.find('.astra-shop-thumbnail-wrap img, img.wp-post-image').first();
        var imageSrc = popupData && popupData.imageSrc ? popupData.imageSrc : ($image.attr('src') || '');
        var imageAlt = $image.attr('alt') || title;
        var imageHtml = '';

        if (imageSrc) {
            imageHtml = '<div class="onepaqucpro-variation-modal__media">' +
                '<img src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(imageAlt) + '">' +
                '</div>';
        }

        var modalHtml = [
            '<div class="onepaqucpro-variation-modal">',
            '<div class="onepaqucpro-variation-modal__backdrop"></div>',
            '<div class="onepaqucpro-variation-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="onepaqucpro-variation-modal-title">',
            '<button type="button" class="onepaqucpro-variation-modal__close" aria-label="Close variation picker">&times;</button>',
            '<div class="onepaqucpro-variation-modal__hero">',
            imageHtml,
            '<div class="onepaqucpro-variation-modal__summary">',
            '<h3 id="onepaqucpro-variation-modal-title" class="onepaqucpro-variation-modal__title">' + escapeHtml(title) + '</h3>',
            priceHtml ? '<div class="onepaqucpro-variation-modal__price">' + priceHtml + '</div>' : '',
            shortDescription ? '<p class="onepaqucpro-variation-modal__text">' + escapeHtml(shortDescription) + '</p>' : '',
            '</div>',
            '</div>',
            '<div class="onepaqucpro-variation-modal__options"></div>',
            '<p class="onepaqucpro-variation-modal__feedback" aria-live="polite"></p>',
            '<div class="onepaqucpro-variation-modal__actions">',
            '<button type="button" class="onepaqucpro-variation-modal__action onepaqucpro-variation-modal__action--cart">Add to cart</button>',
            '<button type="button" class="onepaqucpro-variation-modal__action onepaqucpro-variation-modal__action--buy">Buy now</button>',
            '</div>',
            '</div>',
            '</div>'
        ].join('');

        var $modal = $(modalHtml);
        var $placeholder = $();
        var wasOverlay = false;
        var wasBottomOffset = false;

        if (!isSynthetic) {
            $placeholder = $('<div class="onepaqucpro-variation-modal__placeholder" aria-hidden="true"></div>');
            wasOverlay = $container.hasClass('overlay-variations');
            wasBottomOffset = $container.hasClass('bottom-48');

            $container.after($placeholder);
            $container.removeClass('overlay-variations bottom-48').addClass('onepaqucpro-variation-modal__moved-options');
        }

        $modal.find('.onepaqucpro-variation-modal__options').append($container);
        $('body').append($modal).addClass('onepaqucpro-variation-modal-open');

        if ($container.is('[data-layout="separate"]') && typeof window.onepaqucproInitSeparateArchiveVariations === 'function') {
            window.onepaqucproInitSeparateArchiveVariations($container);
        }

        archiveVariationModalState = {
            $modal: $modal,
            $button: $button,
            $container: $container,
            $placeholder: $placeholder,
            popupData: popupData,
            defaultSummary: {
                title: title,
                priceHtml: priceHtml,
                imageSrc: imageSrc,
                imageAlt: imageAlt
            },
            isSynthetic: isSynthetic,
            wasOverlay: wasOverlay,
            wasBottomOffset: wasBottomOffset
        };

        updateArchiveVariationModalSummary(collectVariationState($button, $button.data('product-id'), $container));
        setArchiveVariationModalFeedback(message || '', message ? 'error' : '');

        $(document).on('keydown.onepaqucproVariationModal', function (event) {
            if (event.key === 'Escape') {
                closeArchiveVariationPopup();
            }
        });

        $modal.find('.onepaqucpro-variation-modal__close').trigger('focus');
        return true;
    }

    function ajaxAddArchiveSelectionToCart($button, selection, callbacks) {
        callbacks = callbacks || {};

        $.ajax({
            type: 'POST',
            url: onepaqucpro_wc_cart_params.ajax_url,
            data: {
                action: 'onepaqucpro_ajax_add_to_cart',
                product_id: $button.data('product-id'),
                quantity: selection.quantity,
                variation_id: selection.variationId,
                variations: selection.variations,
                nonce: onepaqucpro_wc_cart_params.nonce || '',
            },
            success: function (response) {
                if (response.success) {
                    handleWooFragments(response);

                    const shouldOpenSideCart = methodKey === 'side_cart' && !$isonepagewidget && !hasInlineCheckoutFormOnPage();

                    debouncedUpdate(shouldOpenSideCart);
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                    if (callbacks.success) {
                        callbacks.success(response, shouldOpenSideCart);
                    }
                } else if (callbacks.error) {
                    callbacks.error(response.message || 'Could not add the product to cart.');
                }
            },
            error: function () {
                if (callbacks.error) {
                    callbacks.error('Failed to add product to cart. Please try again later.');
                }
            },
            complete: function () {
                if (callbacks.complete) {
                    callbacks.complete();
                }
            }
        });
    }

    function startDirectCheckout($button, selectionOverride) {
        var product_id = $button.data('product-id');
        var product_type = $button.data('product-type');

        $button.addClass('loading').prop('disabled', true);
        appendHiddenCartDrawerStyle();
        directcheckout(product_id, product_type, $button, selectionOverride);
    }

    function directcheckout(product_id, product_type, $button, selectionOverride) {
        var selection = selectionOverride || collectVariationState($button, product_id);
        var $variation_id = selection.variationId;
        var variations = selection.variations;
        var quantity = selection.quantity;
        $('#checkout-button-drawer-link').prop('disabled', true);

        if (product_type === 'variable') {
            var validation = validateVariationSelection(product_type, selection);

            if (!validation.valid) {
                $('#checkout-button-drawer-link').prop('disabled', false);
                $button.removeClass('loading').prop('disabled', false);

                if (openArchiveVariationPopup($button, validation.message)) {
                    $('#cart-drawer2-style').remove();
                    return false;
                }

                alert(validation.message);
                return false;
            }
        }

        // Handle confirmation if enabled
        if ($directbehave.rmenupro_wc_add_confirmation == 1) {
            var methodMap = {
                direct_checkout: "Redirect to Checkout",
                ajax_add: "AJAX Add to Cart",
                cart_redirect: "Redirect to Cart Page",
                popup_checkout: "Popup Checkout",
                side_cart: "Side Cart Slide-in"
            };

            var methodLabel = methodMap[methodKey] || "Direct Checkout";
            var confirmMessage = `Are you sure you want to proceed with ${methodLabel}?`;

            if ($directbehave.rmenupro_wc_clear_cart === "1") {
                confirmMessage += ` This will clear your current cart.`;
            }

            var confirmed = confirm(confirmMessage);

            if (!confirmed) {
                $('#checkout-button-drawer-link').prop('disabled', false);
                $button.removeClass('loading').prop('disabled', false);
                return;
            }
        }

        // Function to proceed with adding to cart        
        function proceedToAddToCart() {
            $.ajax({
                type: 'POST',
                url: onepaqucpro_wc_cart_params.ajax_url,
                data: {
                    action: 'onepaqucpro_ajax_add_to_cart',
                    product_id: product_id,
                    quantity: quantity,
                    variation_id: $variation_id,
                    variations: variations,
                    nonce: onepaqucpro_wc_cart_params.nonce || '',
                },
                success: function (response) {
                    if (response.success) {
                        handleWooFragments(response);

                        const shouldOpenSideCart = methodKey === 'side_cart' && !$isonepagewidget && !hasInlineCheckoutFormOnPage();

                        if (shouldOpenSideCart && $('#cart-drawer2-style').length) {
                            $('#cart-drawer2-style').remove();
                        }

                        // Update UI
                        debouncedUpdate(shouldOpenSideCart);

                        const cartDrawer = $('.cart-drawer');

                        // Trigger WooCommerce hook
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                        // Redirect or UI handling
                        if (methodKey === 'ajax_add') {
                            if (cartDrawer && cartDrawer.length) cartDrawer.removeClass('open');
                            if ($('#cart-drawer2-style').length) $('#cart-drawer2-style').remove();
                        } else if (shouldOpenSideCart) {
                            if (!cartDrawer.length) {
                                console.error('Cart drawer not found. Enable floating/sticky cart from settings.');
                            }
                        } else {
                            window.openCheckoutPopup();
                        }
                    } else {
                        alert(response.message || 'Could not add the product to cart.');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    alert('Failed to add product to cart. Please try again later.');
                },
                complete: function () {
                    $('#checkout-button-drawer-link').prop('disabled', false);
                    $button.removeClass('loading').prop('disabled', false);

                    if ($isonepagewidget) {
                        const element = document.getElementById('checkout-popup');
                        if (element) {
                            element.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                }
            });
        }

        var $redirecturlparams = `?onepaqucpro_add-to-cart=${product_id}&onepaqucpro_quantity=${quantity}`;
        if ($variation_id && $variation_id != 0) {
            $redirecturlparams += `&onepaqucpro_variation_id=${$variation_id}`;
        }

        if (variations && Object.keys(variations).length > 0) {
            $redirecturlparams += `&onepaqucpro_variations=${encodeURIComponent(JSON.stringify(variations))}`;
        }

        // If clear cart is enabled, clear the cart before proceeding
        if ($directbehave.rmenupro_wc_clear_cart == 1) {
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: {
                    action: 'woocommerce_clear_cart'
                },
                success: function () {
                    if (methodKey === 'direct_checkout' && !$isonepagewidget) {
                        window.location.href = onepaqucpro_wc_cart_params.checkout_url + $redirecturlparams;
                    } else if (methodKey === 'cart_redirect' && !$isonepagewidget) {
                        window.location.href = onepaqucpro_wc_cart_params.cart_url + $redirecturlparams;
                    } else {
                        proceedToAddToCart();
                    }
                },
                error: function () {
                    alert('Could not clear cart. Please try again.');
                    $('#checkout-button-drawer-link').prop('disabled', false);
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        } else {
            if (methodKey === 'direct_checkout' && !$isonepagewidget) {
                window.location.href = onepaqucpro_wc_cart_params.checkout_url + $redirecturlparams;
            } else if (methodKey === 'cart_redirect' && !$isonepagewidget) {
                window.location.href = onepaqucpro_wc_cart_params.cart_url + $redirecturlparams;
            } else {
                proceedToAddToCart();
            }
        }
    }

    // Event delegation for better performance
    $(document).on('click', '.direct-checkout-button', function (e) {
        e.preventDefault();
        startDirectCheckout($(this));
    });

    $(document).on('click', '.onepaqucpro-variation-modal__backdrop, .onepaqucpro-variation-modal__close', function (e) {
        e.preventDefault();
        closeArchiveVariationPopup();
    });

    $(document).on('click', '.onepaqucpro-variation-modal .variation-button, .onepaqucpro-variation-modal .var-attr-option', function () {
        if (!archiveVariationModalState) {
            return;
        }

        setTimeout(function () {
            if (!archiveVariationModalState) {
                return;
            }

            var $button = archiveVariationModalState.$button;
            var selection = collectVariationState($button, $button.data('product-id'), archiveVariationModalState.$container);
            var validation = validateVariationSelection($button.data('product-type'), selection);

            updateArchiveVariationModalSummary(selection);

            if (validation.valid) {
                setArchiveVariationModalFeedback('', '');
            }
        }, 0);
    });

    $(document).on('click', '.onepaqucpro-variation-modal__action--buy', function (e) {
        e.preventDefault();

        if (!archiveVariationModalState) {
            return;
        }

        var $button = archiveVariationModalState.$button;
        var selection = collectVariationState($button, $button.data('product-id'), archiveVariationModalState.$container);
        var validation = validateVariationSelection($button.data('product-type'), selection);

        if (!validation.valid) {
            setArchiveVariationModalFeedback(validation.message, 'error');
            return;
        }

        closeArchiveVariationPopup();
        startDirectCheckout($button, selection);
    });

    $(document).on('click', '.onepaqucpro-variation-modal__action--cart', function (e) {
        e.preventDefault();

        if (!archiveVariationModalState) {
            return;
        }

        var $button = archiveVariationModalState.$button;
        var selection = collectVariationState($button, $button.data('product-id'), archiveVariationModalState.$container);
        var validation = validateVariationSelection($button.data('product-type'), selection);

        if (!validation.valid) {
            setArchiveVariationModalFeedback(validation.message, 'error');
            return;
        }

        setArchiveVariationModalFeedback('', '');
        setArchiveVariationModalLoading(true);

        ajaxAddArchiveSelectionToCart($button, selection, {
            success: function () {
                closeArchiveVariationPopup();
            },
            error: function (message) {
                setArchiveVariationModalFeedback(message, 'error');
            },
            complete: function () {
                setArchiveVariationModalLoading(false);
            }
        });
    });

    $(document.body).on('updated_checkout', function () {
        // Get the full HTML of the order total amount from the specified element
        var orderTotalHtml = $('.order-total .woocommerce-Price-amount').html();
        if (orderTotalHtml) orderTotalHtml.trim();
        // Check if the <p> with the class 'order-total-price' exists
        var totalPriceElement = $('.form-row.place-order p.order-total-price');

        if (totalPriceElement.length) {
            // If it exists, update the HTML
            totalPriceElement.html('<span>Total: </span>' + orderTotalHtml);
        } else {
            // If it doesn't exist, prepend a new <p> with the class
            var newTotalParagraph = '<p class="order-total-price"><span>Total: </span>' + orderTotalHtml + '</p>';
            $('.form-row.place-order').prepend(newTotalParagraph);
        }
    });


    function setbtnLoadingState($button, loading) {

        if (loading) {
            // Store the original text if not already stored
            if (!$button.data('original-text')) {
                $button.data('original-text', $button.text());
            }
            $button
                .addClass("loading")
                .prop('disabled', true)
                .text('Adding...');
        } else {
            var originalText = $button.data('original-text') || 'Add to Cart';
            $button
                .removeClass("loading")
                .prop('disabled', false)
                .text(originalText);
        }
    }

    $(document).on('click', '.add-to-cart-button', function (e) {
        e.preventDefault();
        var $button = $(this);
        setbtnLoadingState($button, true);
        const couponMessage = document.getElementById('coupon-message');

        const productId = this.dataset.productId;

        if (!productId) {
            if (couponMessage) {
                couponMessage.textContent = 'Unable to add product. Invalid product ID.';
                couponMessage.className = 'coupon-message error';
                couponMessage.style.display = "block";
            }
            setbtnLoadingState($button, false);
            return;
        }

        const data = {
            action: 'onepaqucpro_ajax_add_to_cart',
            product_id: productId,
            nonce: onepaqucpro_wc_cart_params.nonce || '',
        };

        jQuery.post(onepaqucpro_wc_cart_params.ajax_url, data)
            .done(function (response) {
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (error) {
                        response = { success: false, message: 'Unexpected server response.' };
                    }
                }

                if (response && response.success) {
                    const cart_items = document.querySelector('.cart-items');
                    if (cart_items && response.cart_items_html) {
                        cart_items.innerHTML = response.cart_items_html; // Use innerHTML
                    }

                    // Update cart count
                    const cartCount = document.querySelector('span.cart-count');
                    if (cartCount) {
                        cartCount.textContent = response.cart_count;
                    }

                    // Show success message
                    if (couponMessage) {
                        couponMessage.textContent = 'Product added to cart!';
                        couponMessage.className = 'coupon-message success';
                        couponMessage.style.display = "block";
                    }

                    // Trigger WooCommerce hook
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);

                    debouncedUpdate();

                    // Clear message after delay
                    setTimeout(() => {
                        if (couponMessage) {
                            couponMessage.textContent = '';
                            couponMessage.className = 'coupon-message';
                            couponMessage.style.display = "none";
                        }
                    }, 3000);
                } else {
                    const errorMessage = (response && response.message) ? response.message : 'Could not add product to cart.';
                    if (couponMessage) {
                        couponMessage.textContent = errorMessage;
                        couponMessage.className = 'coupon-message error';
                        couponMessage.style.display = "block";
                    } else {
                        alert(errorMessage);
                    }
                }
            })
            .fail(function () {
                const errorMessage = 'Failed to add product to cart. Please try again.';
                if (couponMessage) {
                    couponMessage.textContent = errorMessage;
                    couponMessage.className = 'coupon-message error';
                    couponMessage.style.display = "block";
                } else {
                    alert(errorMessage);
                }
            })
            .always(function () {
                setbtnLoadingState($button, false);
            });

    });

});







