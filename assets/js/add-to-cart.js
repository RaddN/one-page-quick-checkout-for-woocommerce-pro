/**
 * Restaurant Menu Add to Cart JavaScript
 * add-to-cart.js
 * Handles AJAX add to cart functionality, animations, and notifications
 */

(function ($) {
    'use strict';

    var RMENUPRO_Add_To_Cart = {

        init: function () {
            // Handle AJAX add to cart for archive/shop pages
            $(document).on('click', '.rmenupro-ajax-add-to-cart,.single_add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):not(.product_type_variable),.add_to_cart_button:not(.direct-checkout-button):not(.onepaquc-checkout-btn):not(.product_type_variable)', this.ajaxAddToCart);

            // Handle quantity changes on archive pages
            $(document).on('change', '.rmenupro-archive-quantity', this.updateAddToCartQuantity);

            // Initialize animations
            this.initAnimations();
        },

        /**
         * AJAX add to cart handler
         */
        ajaxAddToCart: function (e) {
            e.preventDefault();

            var $thisButton = $(this);
            var $form = $thisButton.closest('form.cart');
            var productId = $thisButton.data('product_id') || $thisButton.val() || $('input[name="product_id"]').val();

            // Get default quantity from button data attribute
            var defaultQty = $thisButton.data('default_qty') || 1;
            var quantity = defaultQty; // Start with default quantity

            // Try multiple approaches to find the quantity input

            // 1. Check product form first
            if ($form.length > 0) {
                quantity = $form.find('input.qty').val();
            } else {
                // 2. Check for quantity input in sibling elements
                var $qtyInput = $thisButton.siblings('.rmenupro-archive-quantity');
                if ($qtyInput.length > 0) {
                    quantity = $qtyInput.val();
                } else {
                    // 3. Check for quantity in the closest wrapper by product ID
                    var $qtyWrapper = $('.rmenupro-quantity-wrapper[data-product_id="' + productId + '"]');
                    if ($qtyWrapper.length > 0) {
                        var $qtyField = $qtyWrapper.find('.rmenupro-archive-quantity');
                        if ($qtyField.length > 0) {
                            quantity = $qtyField.val();
                        }
                    } else {
                        // 4. Try to find by ID
                        var $qtyById = $('#quantity_' + productId);
                        if ($qtyById.length > 0) {
                            quantity = $qtyById.val();
                        }
                    }
                }
            }

            // Ensure we have a valid quantity
            if (!quantity || quantity < 1 || isNaN(quantity)) {
                quantity = defaultQty;
            }

            // Get variation data if available
            var variationId = 0;
            var variations = {};

            if ($form.length > 0 && $form.find('input[name="variation_id"]').length > 0) {
                variationId = $form.find('input[name="variation_id"]').val();

                // Get all variation attributes
                $form.find('.variations select').each(function () {
                    var attribute = $(this).attr('name');
                    variations[attribute] = $(this).val();
                });
            }

            // Disable button and show loading state
            $thisButton.addClass('loading').prop('disabled', true);

            if (Object.keys(variations).length && variationId == 0) {
                $('#checkout-button-drawer-link').prop('disabled', false);
                $thisButton.removeClass('loading').prop('disabled', false);
                alert("Please select some product options before adding this product to your cart.");
                return false; // Explicitly return false
            }

            var redirecturlparams = `?add-to-cart=${productId}`;
            if (variationId && variationId != 0) {
                redirecturlparams += `&variation_id=${variationId}`;
            }

            if (rmenupro_ajax_object.redirect && rmenupro_ajax_object.redirect_url !== 'none') {
                // Redirect after a short delay to allow animation to show
                window.location.href = rmenupro_ajax_object.redirect_url + redirecturlparams;
            } else {

                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: rmenupro_ajax_object.ajax_url,
                    data: {
                        action: 'onepaqucpro_ajax_add_to_cart',
                        product_id: productId,
                        quantity: quantity,
                        variation_id: variationId,
                        variations: variations,
                        nonce: rmenupro_ajax_object.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            // Re-enable button
                            $thisButton.removeClass('loading').prop('disabled', false);

                            // Handle fragments if any
                            if (response.fragments) {
                                $.each(response.fragments, function (key, value) {
                                    $(key).replaceWith(value);
                                });

                                if (typeof sessionStorage !== 'undefined') {
                                    sessionStorage.setItem('wc_fragments', JSON.stringify(response.fragments));
                                    sessionStorage.setItem('wc_cart_hash', response.cart_hash);
                                }
                            }

                            // If no redirect, show animation and notification
                            RMENUPRO_Add_To_Cart.triggerAnimation($thisButton, response.product_name);
                            RMENUPRO_Add_To_Cart.showNotification(response);

                            // Trigger WC events
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisButton]);
                        } else {
                            // Show error
                            RMENUPRO_Add_To_Cart.showError(response.message || 'Error adding to cart');
                            $thisButton.removeClass('loading').prop('disabled', false);
                        }
                    },
                    error: function () {
                        // Show error
                        RMENUPRO_Add_To_Cart.showError('Server error. Please try again.');
                        $thisButton.removeClass('loading').prop('disabled', false);
                    }
                });
            }
        },

        /**
         * Update quantity when changed on archive pages
         */
        updateAddToCartQuantity: function () {
            var $this = $(this);
            var quantity = $this.val();

            // Try to find the add to cart button in various ways

            // 1. Direct sibling
            var $addToCartButton = $this.siblings('.add_to_cart_button');

            // 2. If in a wrapper with product ID
            if ($addToCartButton.length === 0) {
                var $wrapper = $this.closest('.rmenupro-quantity-wrapper');
                if ($wrapper.length > 0) {
                    var productId = $wrapper.data('product_id');
                    if (productId) {
                        // Find button anywhere on the page with this product ID
                        $addToCartButton = $('.add_to_cart_button[data-product_id="' + productId + '"]');
                    }
                }
            }

            // 3. Check parent elements
            if ($addToCartButton.length === 0) {
                var $product = $this.closest('.product');
                if ($product.length > 0) {
                    $addToCartButton = $product.find('.add_to_cart_button');
                }
            }

            // Update button data-quantity attribute
            if ($addToCartButton.length > 0) {
                $addToCartButton.attr('data-quantity', quantity);
            }
        },

        /**
         * Initialize animations
         */
        initAnimations: function () {
            // Create animation elements if needed
            if (rmenupro_ajax_object.animation === 'fly') {
                $('body').append('<div id="rmenupro-fly-item" class="rmenupro-fly-item"></div>');
            }
        },

        /**
         * Trigger add to cart animation
         */
        triggerAnimation: function ($button, productName) {
            switch (rmenupro_ajax_object.animation) {
                case 'slide':
                    this.slideAnimation($button);
                    break;

                case 'fade':
                    this.fadeAnimation($button);
                    break;

                case 'fly':
                    this.flyToCartAnimation($button, productName);
                    break;

                default:
                    // No animation
                    break;
            }
        },

        /**
         * Slide animation
         */
        slideAnimation: function ($button) {
            $button.addClass('rmenupro-added')
                .append('<span class="rmenupro-check">✓</span>');

            setTimeout(function () {
                $button.find('.rmenupro-check').remove();
                $button.removeClass('rmenupro-added');
            }, 1500);
        },

        /**
         * Fade animation
         */
        fadeAnimation: function ($button) {
            $button.fadeOut(200).fadeIn(200).fadeOut(200).fadeIn(200);
        },

        /**
         * Fly to cart animation
         */
        flyToCartAnimation: function ($button, productName) {
            var $flyItem = $('#rmenupro-fly-item');
            var $cart = $('.cart-contents, .cart-icon, .site-header-cart');

            // If there's no cart icon found, try to find one with common classes
            if ($cart.length === 0) {
                $cart = $('.cart-icon, .cart-contents, .cart-button, .mini-cart');
            }

            // If we still can't find a cart icon, just use the top right corner
            if ($cart.length === 0) {
                $cart = $('body');
            }

            // Get button position
            var buttonPos = $button.offset();
            var cartPos = $cart.offset();

            // Set start position
            $flyItem.html(productName).css({
                'top': buttonPos.top + 'px',
                'left': buttonPos.left + 'px',
                'opacity': 1
            }).show();

            // Animate to cart
            $flyItem.animate({
                'top': cartPos.top + 'px',
                'left': cartPos.left + 'px',
                'opacity': 0,
                'width': '50px',
                'height': '50px',
                'font-size': '0'
            }, 1000, 'swing', function () {
                $(this).hide().css({
                    'width': 'auto',
                    'height': 'auto',
                    'font-size': '14px'
                });
            });
        },

        /**
         * Show add to cart notification based on settings
         */
        showNotification: function (response) {
            var successMsg = rmenupro_ajax_object.i18n.success.replace('{product}', response.product_name);
            var viewCartBtn = rmenupro_ajax_object.i18n.view_cart ? '<a href="' + response.cart_url + '" class="button wc-forward">' + rmenupro_ajax_object.i18n.view_cart + '</a>' : '';
            var checkoutBtn = rmenupro_ajax_object.i18n.checkout ? '<a href="' + response.checkout_url + '" class="button checkout wc-forward">' + rmenupro_ajax_object.i18n.checkout + '</a>' : '';

            switch (rmenupro_ajax_object.notification_style) {
                case 'popup':
                    this.showPopupNotification(successMsg, viewCartBtn, checkoutBtn);
                    break;

                case 'toast':
                    this.showToastNotification(successMsg, viewCartBtn, checkoutBtn);
                    break;

                case 'mini_cart':
                    this.showMiniCartNotification();
                    break;

                default:
                    // Default WooCommerce notices are handled by WooCommerce itself
                    break;
            }
        },

        /**
         * Show popup notification
         */
        showPopupNotification: function (message, viewCartBtn, checkoutBtn) {
            // Remove any existing popups
            $('.rmenupro-popup-notification').remove();

            // Create popup
            var popup = $('<div class="rmenupro-popup-notification">' +
                '<div class="rmenupro-popup-content">' +
                '<div class="rmenupro-popup-message">' + message + '</div>' +
                '<div class="rmenupro-popup-buttons">' + viewCartBtn + checkoutBtn + '</div>' +
                '<span class="rmenupro-popup-close">×</span>' +
                '</div>' +
                '</div>');

            // Add to body
            $('body').append(popup);

            // Show popup
            setTimeout(function () {
                popup.addClass('show');
            }, 10);

            // Handle close button
            popup.find('.rmenupro-popup-close').on('click', function () {
                popup.removeClass('show');
                setTimeout(function () {
                    popup.remove();
                }, 300);
            });

            // Auto-hide after duration
            setTimeout(function () {
                popup.removeClass('show');
                setTimeout(function () {
                    popup.remove();
                }, 300);
            }, rmenupro_ajax_object.notification_duration);
        },

        /**
         * Show toast notification
         */
        showToastNotification: function (message, viewCartBtn, checkoutBtn) {
            // Remove any existing toasts
            $('.rmenupro-toast-notification').remove();

            // Create toast
            var toast = $('<div class="rmenupro-toast-notification">' +
                '<div class="rmenupro-toast-message">' + message + '</div>' +
                '<div class="rmenupro-toast-buttons">' + viewCartBtn + checkoutBtn + '</div>' +
                '</div>');

            // Add to body
            $('body').append(toast);

            // Show toast
            setTimeout(function () {
                toast.addClass('show');
            }, 10);

            // Auto-hide after duration
            setTimeout(function () {
                toast.removeClass('show');
                setTimeout(function () {
                    toast.remove();
                }, 300);
            }, rmenupro_ajax_object.notification_duration);
        },

        /**
         * Show mini cart notification
         */
        showMiniCartNotification: function () {
            var $miniCart = $('.widget_shopping_cart_content').closest('.widget');

            if ($miniCart.length === 0) {
                // If mini cart not found, try to find dropdown cart
                $miniCart = $('.site-header-cart .widget_shopping_cart');
            }

            if ($miniCart.length > 0) {
                $miniCart.addClass('rmenupro-mini-cart-active');

                setTimeout(function () {
                    $miniCart.removeClass('rmenupro-mini-cart-active');
                }, rmenupro_ajax_object.notification_duration);
            }
        },

        /**
         * Show error message
         */
        showError: function (message) {
            // Create error toast
            var errorToast = $('<div class="rmenupro-toast-notification rmenupro-error">' +
                '<div class="rmenupro-toast-message">' + message + '</div>' +
                '</div>');

            // Add to body
            $('body').append(errorToast);

            // Show toast
            setTimeout(function () {
                errorToast.addClass('show');
            }, 10);

            // Auto-hide after duration
            setTimeout(function () {
                errorToast.removeClass('show');
                setTimeout(function () {
                    errorToast.remove();
                }, 300);
            }, rmenupro_ajax_object.notification_duration);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        RMENUPRO_Add_To_Cart.init();
    });

})(jQuery);