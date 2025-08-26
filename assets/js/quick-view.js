/**
 * WooCommerce Quick View JavaScript
 * 
 * Handles the frontend functionality for the Quick View feature.
 */
(function ($) {
    'use strict';

    // Quick View Object
    var RMenuQuickView = {
        modal: null,
        overlay: null,
        content: null,
        closeBtn: null,
        loading: null,
        prevBtn: null,
        nextBtn: null,
        currentProductId: null,
        isLoading: false,
        settings: rmenupro_quick_view_params,
        productsData: {}, // Store all product data here
        productsLoaded: false, // Track if products are loaded


        /**
         * Initialize the Quick View functionality
         */
        init: function () {
            // Cache DOM elements
            var self = this;
            this.modal = $('.opqvfw-modal-container');
            this.overlay = this.modal.find('.opqvfw-modal-overlay');
            this.content = this.modal.find('.rmenupro-quick-view-inner');
            this.closeBtn = this.modal.find('.rmenupro-quick-view-close');
            this.loading = this.modal.find('.rmenupro-quick-view-loading');
            this.prevBtn = this.modal.find('.rmenupro-quick-view-prev');
            this.nextBtn = this.modal.find('.rmenupro-quick-view-next');


            // Bind events
            this.bindEvents();

            // Mobile optimization
            if (this.settings.mobile_optimize) {
                this.mobileOptimize();
            }

            // Trigger init event
            $(document.body).trigger('rmenupro_quick_view_init');

            if ($('.rmenu-product-data[data-product-info]').length < 1) {
                // Preload all products data
                setTimeout(function () {
                    self.loadAllProductsData();
                }, 100); // Small delay to ensure DOM is ready
            }
        },

        /**
         * Bind all necessary events
         */
        bindEvents: function () {
            var self = this;

            // Quick view button click
            $(document.body).on('click', '.opqvfw-btn', function (e) {
                e.preventDefault();
                var productId = $(this).data('product-id');
                self.openQuickView(productId);
                return false;
            });

            // Close quick view
            this.closeBtn.on('click', function (e) {
                e.preventDefault();
                self.closeQuickView();
                return false;
            });

            // Close on overlay click
            this.overlay.on('click', function (e) {
                if (e.target === this) {
                    self.closeQuickView();
                    return false;
                }
            });

            // Previous/Next buttons
            this.prevBtn.on('click', function (e) {
                e.preventDefault();
                self.navigateProduct('prev');
                return false;
            });

            this.nextBtn.on('click', function (e) {
                e.preventDefault();
                self.navigateProduct('next');
                return false;
            });

            // Keyboard navigation
            if (this.settings.keyboard_nav) {
                $(document).on('keydown', function (e) { // Changed from keyup to keydown for better responsiveness
                    if (!self.modal.hasClass('active')) {
                        return;
                    }

                    // ESC key
                    if (e.keyCode === 27) {
                        self.closeQuickView();
                    }
                    // Left arrow
                    else if (e.keyCode === 37) {
                        self.navigateProduct('prev');
                    }
                    // Right arrow
                    else if (e.keyCode === 39) {
                        self.navigateProduct('next');
                    }
                });
            }

            // Gallery thumbnails
            this.modal.on('click', '.rmenupro-quick-view-thumbnail', function () {
                var $this = $(this);
                var imageId = $this.data('image-id');
                var fullImage = $this.data('full-image');

                // Update active thumbnail
                self.modal.find('.rmenupro-quick-view-thumbnail').removeClass('active');
                $this.addClass('active');

                // Update main image
                var $mainImage = self.modal.find('.rmenupro-quick-view-main-image img');
                var $lightboxLink = self.modal.find('.rmenupro-quick-view-lightbox');

                // If we're using lightbox and have a full image URL
                if (self.settings.lightbox && fullImage) {
                    $lightboxLink.attr('href', fullImage);
                }

                // Fade out/in the image for smooth transition
                $mainImage.fadeOut(100, function () {
                    // Find the new image in data attributes
                    var newImage = $this.data('large-image') || $this.find('img').attr('src').replace('-thumbnail', '');
                    $(this).attr('src', newImage).fadeIn(100);
                });
            });

            // Initialize lightbox if enabled
            if (this.settings.lightbox) {
                this.initLightbox();
            }

            // Variation select handling
            this.modal.on('show_variation', function (event, variation) {
                if (variation && variation.image && variation.image.src) {
                    var $mainImage = self.modal.find('.rmenupro-quick-view-main-image img');
                    var $lightboxLink = self.modal.find('.rmenupro-quick-view-lightbox');

                    $mainImage.attr('src', variation.image.src).attr('srcset', '');

                    if (self.settings.lightbox && variation.image.full_src) {
                        $lightboxLink.attr('href', variation.image.full_src);
                    }
                }
            });
        },

        /**
         * Open the quick view modal using product data already stored in DOM
         */
        openQuickView: function (productId) {
            var self = this;

            if (self.isLoading) {
                return;
            }

            self.isLoading = true;
            self.currentProductId = productId;

            // Show modal with loading indicator
            self.showModal();
            self.loading.show();
            self.content.empty();

            // Find the product data from the DOM
            var $productElement = $('.rmenupro-product-data[data-product-info]').filter(function () {
                var productInfo = $(this).data('product-info');
                return productInfo && productInfo.id == productId;
            }).first();

            if ($productElement.length) {
                // Use the embedded product data
                var productData = $productElement.data('product-info');
                self.renderProductContent(productData);
            } else {
                // Fallback to AJAX load if we don't have the data
                self.loadProductContent(productId);
            }
        },

        /**
         * Render product content based on JSON data
         */
        renderProductContent: function (productData) {
            var self = this;
            var html = '';

            // Get the elements to display
            var elements = self.settings.elements_in_popup || ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta'];

            // Start building the HTML
            html += '<div class="rmenupro-quick-view-product">';

            // Left column (image)
            html += '<div class="rmenupro-quick-view-left">';
            if ($.inArray('image', elements) !== -1 && productData.images.length > 0) {
                html += '<div class="rmenupro-quick-view-images">';
                // Main image
                html += '<div class="rmenupro-quick-view-main-image">';
                if (productData.images.length > 1) {
                    html += '<a href="' + productData.images[0].full + '" class="rmenupro-quick-view-lightbox">';
                } else {
                    html += '<a href="' + productData.images[0].full + '" class="rmenupro-quick-view-image">';
                }
                html += '<img src="' + productData.images[0].src + '" alt="' + productData.images[0].alt + '">';
                html += '</a>';
                html += '</div>';

                // Thumbnails (if more than one image)
                if ($.inArray('gallery', elements) !== -1 && productData.images.length > 1) {
                    html += '<div class="rmenupro-quick-view-thumbnails">';
                    $.each(productData.images, function (index, image) {
                        var activeClass = index === 0 ? ' active' : '';
                        html += '<div class="rmenupro-quick-view-thumbnail' + activeClass + '" data-image-id="' + image.id + '" data-large-image="' + image.src + '" data-full-image="' + image.full + '">';
                        html += '<img src="' + image.thumb + '" alt="' + image.alt + '">';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                html += '</div>';
            } else {
                // Fallback if no images are available
                html += '<div class="rmenupro-quick-view-images">';
                html += '<div class="rmenupro-quick-view-main-image">';
                html += '<img src="/wp-content/uploads/woocommerce-placeholder-300x300.png" alt="' + self.settings.i18n.no_image + '">';
                html += '</div>';
                html += '</div>';
            }
            html += '</div>';

            // Right column (information)
            html += '<div class="rmenupro-quick-view-right">';

            // Title
            if ($.inArray('title', elements) !== -1) {
                html += '<h2 class="product_title">' + productData.title + '</h2>';
            }

            // Rating
            if ($.inArray('rating', elements) !== -1 && productData.rating_html) {
                html += '<div class="woocommerce-product-rating">' + productData.rating_html + '</div>';
            }

            // Price
            if ($.inArray('price', elements) !== -1) {
                html += '<div class="price">' + productData.price_html + '</div>';
            }

            // Excerpt
            if ($.inArray('excerpt', elements) !== -1 && productData.excerpt) {
                html += '<div class="woocommerce-product-details__short-description">' + productData.excerpt + '</div>';
            }

            // Add to cart form
            if ($.inArray('add_to_cart', elements) !== -1) {
                if (productData.type === 'simple') {
                    // Simple product add to cart
                    html += '<form class="cart rmenupro-add-to-cart-form" method="post" enctype="multipart/form-data">';
                    html += '<input type="hidden" name="add-to-cart" value="' + productData.id + '">';

                    // Quantity field
                    // if ($.inArray('quantity', elements) !== -1) {
                    //     html += '<div class="quantity">';
                    //     html += '<label class="screen-reader-text" for="quantity_' + productData.id + '">' + self.settings.i18n.quantity + '</label>';
                    //     html += '<input type="number" id="quantity_' + productData.id + '" class="input-text qty text" step="1" min="1" max="' + (productData.max_purchase_quantity || '') + '" name="quantity" value="' + (productData.min_purchase_quantity || '1') + '" title="Qty" size="4">';
                    //     html += '</div>';
                    // }

                    // Add to cart button based on stock status
                    if (productData.is_in_stock && productData.is_purchasable) {
                        html += '<a href="?add-to-cart=' + productData.id + '" ' +
                            'data-quantity="1" ' +
                            'class="button product_type_simple add_to_cart_button onepaqucpro_ajax_add_to_cart rmenupro-ajax-add-to-cart" ' +
                            'data-product_id="' + productData.id + '" ' +
                            'data-product_sku="' + (productData.sku || '') + '" ' +
                            'data-default_qty="1" ' +
                            'aria-label="Add to cart: &ldquo;' + productData.title.replace(/"/g, '&quot;') + '&rdquo;" ' +
                            'rel="nofollow" ' +
                            'style="display: inline-flex; align-items: center; justify-content: center;">' +
                            '<span class="rmenupro-btn-icon" style="margin-right: 8px;">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">' +
                            '<circle cx="9" cy="21" r="1"></circle>' +
                            '<circle cx="20" cy="21" r="1"></circle>' +
                            '<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>' +
                            '</svg>' +
                            '</span>' +
                            '<span class="rmenupro-btn-text">' + self.settings.i18n.add_to_cart + '</span>' +
                            '</a>';
                    } else {
                        html += '<button type="button" class="button alt disabled">' + self.settings.i18n.out_of_stock + '</button>';
                    }

                    html += '</form>';
                } else if (productData.type === 'variable') {
                    // For variable products we should show a button to redirect to product page
                    html += '<a href="' + productData.permalink + '" class="button alt">' + self.settings.i18n.select_options + '</a>';
                }
            }

            // Meta information
            if ($.inArray('meta', elements) !== -1) {
                html += '<div class="product_meta">';

                // SKU
                if (productData.sku) {
                    html += '<span class="sku_wrapper"> SKU: <span class="sku">' + productData.sku + '</span></span>';
                }

                // Categories
                if (productData.brands_html) {
                    html += '<span class="posted_in"> Brands: ' + productData.brands_html + '</span>';
                }

                // Categories
                if (productData.categories_html) {
                    html += '<span class="posted_in"> Categories: ' + productData.categories_html + '</span>';
                }

                // Tags
                if (productData.tags_html) {
                    html += '<span class="tagged_as"> Tags: ' + productData.tags_html + '</span>';
                }

                html += '</div>';
            }
            if ($.inArray('view_details', elements) !== -1) {
                // View full details link
                html += '<div class="rmenupro-quick-view-details-button">';
                html += '<a href="' + productData.permalink + '" class="button">' + self.settings.i18n.view_details + '</a>';
                html += '</div>';
            }

            // social sharing buttons
            if ($.inArray('sharing', elements) !== -1) {
                html += '<div class="rmenupro-quick-view-social-share">';
                html += '<h4 style="margin: 8px 0;">' + self.settings.i18n.share_this_product + '</h4>';
                var shareUrl = encodeURIComponent(productData.permalink);
                var shareTitle = encodeURIComponent(productData.title);

                html += '<div style="display: flex; gap: 10px;">';
                // Facebook
                html += '<a href="https://www.facebook.com/sharer/sharer.php?u=' + shareUrl + '" target="_blank" rel="noopener noreferrer" class="facebook-share"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48"><path fill="#039be5" d="M24 5A19 19 0 1 0 24 43A19 19 0 1 0 24 5Z"></path><path fill="#fff" d="M26.572,29.036h4.917l0.772-4.995h-5.69v-2.73c0-2.075,0.678-3.915,2.619-3.915h3.119v-4.359c-0.548-0.074-1.707-0.236-3.897-0.236c-4.573,0-7.254,2.415-7.254,7.917v3.323h-4.701v4.995h4.701v13.729C22.089,42.905,23.032,43,24,43c0.875,0,1.729-0.08,2.572-0.194V29.036z"></path></svg></a>';
                // Twitter/X
                html += '<a href="https://twitter.com/intent/tweet?url=' + shareUrl + '&text=' + shareTitle + '" target="_blank" rel="noopener noreferrer" class="twitter-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 50 50"> <path d="M 11 4 C 7.134 4 4 7.134 4 11 L 4 39 C 4 42.866 7.134 46 11 46 L 39 46 C 42.866 46 46 42.866 46 39 L 46 11 C 46 7.134 42.866 4 39 4 L 11 4 z M 13.085938 13 L 21.023438 13 L 26.660156 21.009766 L 33.5 13 L 36 13 L 27.789062 22.613281 L 37.914062 37 L 29.978516 37 L 23.4375 27.707031 L 15.5 37 L 13 37 L 22.308594 26.103516 L 13.085938 13 z M 16.914062 15 L 31.021484 35 L 34.085938 35 L 19.978516 15 L 16.914062 15 z"></path> </svg>' + '</a>';
                // Pinterest
                html += '<a href="https://pinterest.com/pin/create/button/?url=' + shareUrl + '&description=' + shareTitle + '" target="_blank" rel="noopener noreferrer" class="pinterest-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48"> <linearGradient id="IfhrvZkWi8LOXjspG~Pupa_XErM9A1xNUK5_gr1" x1="14.899" x2="33.481" y1="43.815" y2="7.661" gradientTransform="matrix(1 0 0 -1 .108 50.317)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#f22543"></stop><stop offset=".422" stop-color="#eb2239"></stop><stop offset="1" stop-color="#e52030"></stop></linearGradient><path fill="url(#IfhrvZkWi8LOXjspG~Pupa_XErM9A1xNUK5_gr1)" d="M44,23.9810009C44.0110016,35.026001,35.0639992,43.9889984,24.0189991,44	S4.0110002,35.0639992,4,24.0189991C3.9890001,12.974,12.9359999,4.0110002,23.9810009,4	C35.026001,3.9890001,43.9889984,12.9359999,44,23.9810009z"></path><path d="M37.7729988,22.9680004c0-7.1560001-5.7299995-12.552-13.3299999-12.552	c-9.7670002,0-14.2150002,6.7919998-14.2150002,13.1040001c0,3.1480007,1.625,7.2519989,4.6370001,8.6549988	c0.4860001,0.2270012,0.9300003,0.2439995,1.3210001,0.0550003c0.2609997-0.1259995,0.6030006-0.4029999,0.7420006-0.9950008	l0.5559998-2.2689991c0.1259995-0.5289993,0.0130005-1.0139999-0.3390007-1.4419994	c-0.6259995-0.7600002-1.2609997-2.3540001-1.2609997-3.9300003c0-3.7229996,2.8139992-7.6800003,8.0299988-7.6800003	c4.3299999,0,7.3540001,2.9349995,7.3540001,7.1369991c0,4.7270012-2.223999,8.1580009-5.2870007,8.1580009	c-0.6819992,0-1.289999-0.2740002-1.6679993-0.7520008c-0.3509998-0.4440002-0.4640007-1.0230007-0.3209991-1.6310005	c0.1959991-0.8260002,0.4650002-1.6940002,0.7240009-2.5340004c0.4939995-1.5979996,0.9599991-3.1070004,0.9599991-4.3549995	c0-2.282999-1.4190006-3.816-3.5300007-3.816c-2.5900002,0-4.618,2.5720005-4.618,5.855999	c0,1.4130001,0.3430004,2.5170002,0.5499992,3.0559998c-0.3649998,1.5440006-1.9489994,8.2490005-2.2700005,9.6269989	c-0.3839998,1.6399994-0.3070002,3.8040009-0.1479998,5.5009995c1.0940008,0.5029984,2.2400007,0.9059982,3.4299994,1.2070007	c0.7719994-1.2789993,1.9710007-3.4389992,2.4419994-5.2490005c0.132-0.5079994,0.4880009-1.8660011,0.7859993-3.0009995	c1.0979996,0.7939987,2.585001,1.2910004,4.0760002,1.2910004C32.882,36.4080009,37.7729988,30.6299992,37.7729988,22.9680004z" opacity=".05"></path><path d="M37.2729988,22.9680004c0-6.8710003-5.5159988-12.052-12.8299999-12.052	c-9.4230003,0-13.7150002,6.5330009-13.7150002,12.6040001c0,3.0359993,1.6260004,6.934,4.349,8.2019997	c0.1269999,0.0599995,0.5159998,0.2409992,0.8920002,0.059c0.2379999-0.1159992,0.3979998-0.3390007,0.474-0.6620007	l0.5550003-2.2679996c0.0890007-0.3740005,0.0119991-0.7040005-0.2390003-1.0079994	c-0.809-0.9820004-1.375-2.7290001-1.375-4.2479992c0-3.9650002,2.9899998-8.1800003,8.5299988-8.1800003	c4.6240005,0,7.8540001,3.1399994,7.8540001,7.637001c0,5.0170002-2.434,8.6580009-5.7870007,8.6580009	c-0.8349991,0-1.5860004-0.3430004-2.0599995-0.9419994c-0.448-0.566-0.5949993-1.2970009-0.4150009-2.0569992	c0.2000008-0.842001,0.4710007-1.7180004,0.7329998-2.566c0.4820004-1.5610008,0.9379997-3.0349998,0.9379997-4.2080002	c0-1.9839993-1.2180004-3.316-3.0300007-3.316c-2.309,0-4.118,2.3530006-4.118,5.3560009	c0,1.493,0.3990002,2.6049995,0.573,3.0179996c-0.2789993,1.1800003-1.9729996,8.3479977-2.3059998,9.7790012	c-0.3909998,1.6749992-0.2819996,3.9440002-0.1070004,5.6409988c0.7730007,0.3279991,1.5769997,0.5940018,2.3959999,0.8240013	c0.7520008-1.2330017,1.993-3.4350014,2.4640007-5.2449989c0.1639996-0.6300011,0.6709995-2.5629997,0.9860001-3.762001	c1.0139999,1.0089989,2.6809998,1.6780014,4.3600006,1.6780014C32.5970001,35.9080009,37.2729988,30.3449993,37.2729988,22.9680004z" opacity=".07"></path><path fill="#FFF" d="M24.4430008,11.4169998c-8.632,0-13.2150002,5.7950001-13.2150002,12.1030006	c0,2.9330006,1.5620003,6.5849991,4.0599995,7.7480011c0.3780003,0.177,0.5819998,0.1000004,0.6680002-0.2670002	c0.0670004-0.2779999,0.4030008-1.6369991,0.5549994-2.2679996c0.0480003-0.2019997,0.0249996-0.375-0.1380005-0.573	c-0.8269997-1.0030003-1.4879999-2.8470001-1.4879999-4.5650005c0-4.4120007,3.3399992-8.6800003,9.0299997-8.6800003	c4.9130001,0,8.3529987,3.3479996,8.3529987,8.137001c0,5.4099998-2.7320004,9.1580009-6.2870007,9.1580009	c-1.9629993,0-3.4330006-1.6229992-2.9619999-3.6149998c0.5650005-2.3770008,1.6569996-4.9419994,1.6569996-6.657999	c0-1.5349998-0.823-2.8169994-2.5300007-2.8169994c-2.007,0-3.618,2.0750008-3.618,4.8570004	c0,1.7700005,0.5979996,2.9680004,0.5979996,2.9680004s-1.9820004,8.3819981-2.3449993,9.9419994	c-0.4020004,1.7220001-0.2460003,4.1409988-0.0709991,5.7229996c0.4510002,0.1769981,0.9020004,0.3540001,1.3689995,0.4990005	c0.8169994-1.3279991,2.0340004-3.5060005,2.4860001-5.2420006c0.243-0.9370003,1.2469997-4.7550011,1.2469997-4.7550011	c0.6520004,1.243,2.5569992,2.2970009,4.5830002,2.2970009c6.0320015,0,10.3779984-5.5470009,10.3779984-12.4399986	C36.7729988,16.3600006,31.382,11.4169998,24.4430008,11.4169998z"></path> </svg>' + '</a>';
                // WhatsApp
                html += '<a href="https://wa.me/?text=' + shareTitle + '%20' + shareUrl + '" target="_blank" rel="noopener noreferrer" class="whatsapp-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48"> <path fill="#fff" d="M4.9,43.3l2.7-9.8C5.9,30.6,5,27.3,5,24C5,13.5,13.5,5,24,5c5.1,0,9.8,2,13.4,5.6C41,14.2,43,18.9,43,24	c0,10.5-8.5,19-19,19c0,0,0,0,0,0h0c-3.2,0-6.3-0.8-9.1-2.3L4.9,43.3z"></path><path fill="#fff" d="M4.9,43.8c-0.1,0-0.3-0.1-0.4-0.1c-0.1-0.1-0.2-0.3-0.1-0.5L7,33.5c-1.6-2.9-2.5-6.2-2.5-9.6	C4.5,13.2,13.3,4.5,24,4.5c5.2,0,10.1,2,13.8,5.7c3.7,3.7,5.7,8.6,5.7,13.8c0,10.7-8.7,19.5-19.5,19.5c-3.2,0-6.3-0.8-9.1-2.3	L5,43.8C5,43.8,4.9,43.8,4.9,43.8z"></path><path fill="#cfd8dc" d="M24,5c5.1,0,9.8,2,13.4,5.6C41,14.2,43,18.9,43,24c0,10.5-8.5,19-19,19h0c-3.2,0-6.3-0.8-9.1-2.3L4.9,43.3	l2.7-9.8C5.9,30.6,5,27.3,5,24C5,13.5,13.5,5,24,5 M24,43L24,43L24,43 M24,43L24,43L24,43 M24,4L24,4C13,4,4,13,4,24	c0,3.4,0.8,6.7,2.5,9.6L3.9,43c-0.1,0.3,0,0.7,0.3,1c0.2,0.2,0.4,0.3,0.7,0.3c0.1,0,0.2,0,0.3,0l9.7-2.5c2.8,1.5,6,2.2,9.2,2.2	c11,0,20-9,20-20c0-5.3-2.1-10.4-5.8-14.1C34.4,6.1,29.4,4,24,4L24,4z"></path><path fill="#40c351" d="M35.2,12.8c-3-3-6.9-4.6-11.2-4.6C15.3,8.2,8.2,15.3,8.2,24c0,3,0.8,5.9,2.4,8.4L11,33l-1.6,5.8l6-1.6l0.6,0.3	c2.4,1.4,5.2,2.2,8,2.2h0c8.7,0,15.8-7.1,15.8-15.8C39.8,19.8,38.2,15.8,35.2,12.8z"></path><path fill="#fff" fill-rule="evenodd" d="M19.3,16c-0.4-0.8-0.7-0.8-1.1-0.8c-0.3,0-0.6,0-0.9,0s-0.8,0.1-1.3,0.6c-0.4,0.5-1.7,1.6-1.7,4	s1.7,4.6,1.9,4.9s3.3,5.3,8.1,7.2c4,1.6,4.8,1.3,5.7,1.2c0.9-0.1,2.8-1.1,3.2-2.3c0.4-1.1,0.4-2.1,0.3-2.3c-0.1-0.2-0.4-0.3-0.9-0.6	s-2.8-1.4-3.2-1.5c-0.4-0.2-0.8-0.2-1.1,0.2c-0.3,0.5-1.2,1.5-1.5,1.9c-0.3,0.3-0.6,0.4-1,0.1c-0.5-0.2-2-0.7-3.8-2.4	c-1.4-1.3-2.4-2.8-2.6-3.3c-0.3-0.5,0-0.7,0.2-1c0.2-0.2,0.5-0.6,0.7-0.8c0.2-0.3,0.3-0.5,0.5-0.8c0.2-0.3,0.1-0.6,0-0.8	C20.6,19.3,19.7,17,19.3,16z" clip-rule="evenodd"></path> </svg>' + '</a>';
                // LinkedIn
                html += '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' + shareUrl + '&title=' + shareTitle + '" target="_blank" rel="noopener noreferrer" class="linkedin-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48"> <path fill="#0078d4" d="M42,37c0,2.762-2.238,5-5,5H11c-2.761,0-5-2.238-5-5V11c0-2.762,2.239-5,5-5h26c2.762,0,5,2.238,5,5	V37z"></path><path d="M30,37V26.901c0-1.689-0.819-2.698-2.192-2.698c-0.815,0-1.414,0.459-1.779,1.364	c-0.017,0.064-0.041,0.325-0.031,1.114L26,37h-7V18h7v1.061C27.022,18.356,28.275,18,29.738,18c4.547,0,7.261,3.093,7.261,8.274	L37,37H30z M11,37V18h3.457C12.454,18,11,16.528,11,14.499C11,12.472,12.478,11,14.514,11c2.012,0,3.445,1.431,3.486,3.479	C18,16.523,16.521,18,14.485,18H18v19H11z" opacity=".05"></path><path d="M30.5,36.5v-9.599c0-1.973-1.031-3.198-2.692-3.198c-1.295,0-1.935,0.912-2.243,1.677	c-0.082,0.199-0.071,0.989-0.067,1.326L25.5,36.5h-6v-18h6v1.638c0.795-0.823,2.075-1.638,4.238-1.638	c4.233,0,6.761,2.906,6.761,7.774L36.5,36.5H30.5z M11.5,36.5v-18h6v18H11.5z M14.457,17.5c-1.713,0-2.957-1.262-2.957-3.001	c0-1.738,1.268-2.999,3.014-2.999c1.724,0,2.951,1.229,2.986,2.989c0,1.749-1.268,3.011-3.015,3.011H14.457z" opacity=".07"></path><path fill="#fff" d="M12,19h5v17h-5V19z M14.485,17h-0.028C12.965,17,12,15.888,12,14.499C12,13.08,12.995,12,14.514,12	c1.521,0,2.458,1.08,2.486,2.499C17,15.887,16.035,17,14.485,17z M36,36h-5v-9.099c0-2.198-1.225-3.698-3.192-3.698	c-1.501,0-2.313,1.012-2.707,1.99C24.957,25.543,25,26.511,25,27v9h-5V19h5v2.616C25.721,20.5,26.85,19,29.738,19	c3.578,0,6.261,2.25,6.261,7.274L36,36L36,36z"></path> </svg>' + '</a>';
                // Reddit
                html += '<a href="https://www.reddit.com/submit?url=' + shareUrl + '&title=' + shareTitle + '" target="_blank" rel="noopener noreferrer" class="reddit-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="#ff5722" x="0px" y="0px" width="30" height="30" viewBox="0 0 9 9"><path d="M5.299 0.6C4.669 0.6 4.2 1.114 4.2 1.699v1.012c-0.825 0.044 -1.58 0.264 -2.182 0.609 -0.232 -0.223 -0.543 -0.322 -0.844 -0.322 -0.327 0 -0.661 0.113 -0.891 0.373l-0.005 0.006 -0.005 0.006c-0.221 0.276 -0.308 0.646 -0.25 1.013a1.32 1.32 0 0 0 0.586 0.893C0.606 5.326 0.6 5.362 0.6 5.4c0 1.489 1.75 2.7 3.9 2.7s3.9 -1.211 3.9 -2.7c0 -0.037 -0.006 -0.074 -0.008 -0.111a1.32 1.32 0 0 0 0.586 -0.893c0.059 -0.367 -0.028 -0.737 -0.25 -1.014l-0.005 -0.006 -0.005 -0.006c-0.231 -0.259 -0.565 -0.372 -0.891 -0.373 -0.301 0 -0.612 0.099 -0.844 0.322C6.38 2.974 5.625 2.754 4.8 2.711V1.699C4.8 1.405 4.97 1.2 5.299 1.2c0.156 0 0.346 0.078 0.645 0.183 0.252 0.088 0.58 0.178 0.999 0.204A0.75 0.75 0 0 0 7.65 2.1C8.063 2.1 8.4 1.762 8.4 1.35S8.063 0.6 7.65 0.6c-0.282 0 -0.526 0.159 -0.654 0.39a2.94 2.94 0 0 1 -0.854 -0.173C5.871 0.722 5.621 0.6 5.299 0.6m-4.125 2.998c0.118 0 0.231 0.031 0.324 0.082 -0.331 0.276 -0.582 0.599 -0.734 0.952a0.69 0.69 0 0 1 -0.148 -0.331c-0.032 -0.202 0.028 -0.411 0.124 -0.535 0.09 -0.098 0.255 -0.168 0.435 -0.168m6.652 0c0.179 0 0.345 0.071 0.435 0.168 0.096 0.124 0.156 0.334 0.124 0.535a0.69 0.69 0 0 1 -0.148 0.33c-0.152 -0.352 -0.403 -0.676 -0.734 -0.952a0.69 0.69 0 0 1 0.323 -0.082M3 4.2a0.6 0.6 0 1 1 0 1.2A0.6 0.6 0 0 1 3 4.2m3 0a0.6 0.6 0 1 1 0 1.2A0.6 0.6 0 0 1 6 4.2m0.071 1.66C5.88 6.42 5.267 6.9 4.5 6.9s-1.38 -0.48 -1.571 -1c0.345 0.28 0.92 0.48 1.571 0.48s1.226 -0.2 1.571 -0.52"/></svg>' + '</a>';
                // Email
                html += '<a href="mailto:?subject=' + shareTitle + '&body=' + shareUrl + '" class="email-share">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 14.4 14.4"><path d="M3.15 2.4C2.078 2.4 1.2 3.278 1.2 4.35v5.7c0 1.072 0.878 1.95 1.95 1.95h8.1c1.072 0 1.95 -0.878 1.95 -1.95v-5.7c0 -1.072 -0.878 -1.95 -1.95 -1.95zm0 0.9h8.1c0.178 0 0.342 0.047 0.488 0.124L7.2 5.877 2.662 3.424A1.05 1.05 0 0 1 3.15 3.3m-0.15 2.353 3.772 2.038a0.9 0.9 0 0 0 0.857 0L11.4 5.653V11.085c-0.049 0.007 -0.099 0.015 -0.15 0.015h-8.1c-0.051 0 -0.101 -0.008 -0.15 -0.015z"/></svg>' + '</a>';
                html += '</div></div>';
            }

            html += '</div>'; // End right column
            html += '</div>'; // End product container

            // Render the content
            self.content.html(html);
            self.loading.hide();
            self.updateNavigation();

            // Trigger event
            $(document.body).trigger('rmenupro_quick_view_opened', [productData.id]);

            self.isLoading = false;
        },

        loadAllProductsData: function () {
            var self = this;

            if (self.productsLoaded || self.isLoading) {
                return Promise.resolve();
            }

            // Get all product IDs from the page
            var productIds = [];
            $('.opqvfw-btn').each(function () {
                var productId = $(this).data('product-id');
                if (productId && productIds.indexOf(productId) === -1) {
                    productIds.push(productId);
                }
            });

            if (productIds.length === 0) {
                return Promise.resolve();
            }

            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: self.settings.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rmenu_get_all_products_quick_view',
                        product_ids: productIds,
                        nonce: self.settings.nonce
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            self.productsData = response.data;
                            self.productsLoaded = true;
                            resolve(response.data);
                        } else {
                            reject(response.data || 'Failed to load products');
                        }
                    },
                    error: function () {
                        reject('Ajax error occurred');
                    }
                });
            });
        },

        /**
         * Load product content via AJAX (fallback)
         */
        loadProductContent: function (productId) {
            var self = this;

            // Check if we have the product data cached
            if (self.productsData[productId]) {
                self.renderProductContent(self.productsData[productId]);
                return;
            }

            // If products aren't loaded yet, load them first
            if (!self.productsLoaded) {
                self.loadAllProductsData().then(function () {
                    if (self.productsData[productId]) {
                        self.renderProductContent(self.productsData[productId]);
                    } else {
                        self.content.html('<div class="rmenu-quick-view-error">' + self.settings.i18n.error_loading + '</div>');
                        self.loading.hide();
                        self.isLoading = false;
                    }
                }).catch(function (error) {
                    self.content.html('<div class="rmenu-quick-view-error">' + self.settings.i18n.error_loading + '</div>');
                    self.loading.hide();
                    self.isLoading = false;
                });
            } else {
                // Products loaded but this specific product not found
                self.content.html('<div class="rmenu-quick-view-error">' + self.settings.i18n.error_loading + '</div>');
                self.loading.hide();
                self.isLoading = false;
            }
        },

        /**
         * Show the modal with animation effect
         */
        showModal: function () {
            var self = this;

            // Apply effect based on settings
            switch (self.settings.effect) {
                case 'slide':
                    self.modal.addClass('active slide-in');
                    break;

                case 'zoom':
                    self.modal.addClass('active zoom-in');
                    break;

                case 'none':
                    self.modal.addClass('active');
                    break;

                default: // fade
                    self.modal.addClass('active fade-in');
                    break;
            }

            // Add body class
            $('body').addClass('rmenupro-quick-view-active');
        },

        /**
         * Close the quick view modal
         */
        closeQuickView: function () {
            var self = this;

            // Remove effect classes
            self.modal.removeClass('active slide-in zoom-in fade-in');

            // Clear content after animation
            setTimeout(function () {
                self.content.empty();
                self.currentProductId = null;
                $('body').removeClass('rmenupro-quick-view-active');

                // Trigger closed event
                $(document.body).trigger('rmenupro_quick_view_closed');
            }, 300);
        },

        /**
         * Navigate to previous/next product
         */
        navigateProduct: function (direction) {
            var self = this; // Store reference to 'this'

            if (self.isLoading || !self.currentProductId) {
                return;
            }

            var $productItems = $('.product');
            var currentIndex = -1;

            // Find the current product index
            $productItems.each(function (index) {
                var pid = $(this).find('.opqvfw-btn').data('product-id');
                if (pid == self.currentProductId) { // Use self instead of this
                    currentIndex = index;
                    return false;
                }
            });

            if (currentIndex === -1) {
                return;
            }

            var newIndex;
            if (direction === 'prev') {
                newIndex = currentIndex - 1;
                if (newIndex < 0) {
                    newIndex = $productItems.length - 1;
                }
            } else {
                newIndex = currentIndex + 1;
                if (newIndex >= $productItems.length) {
                    newIndex = 0;
                }
            }

            var $nextProduct = $productItems.eq(newIndex);
            var nextProductId = $nextProduct.find('.opqvfw-btn').data('product-id');

            if (nextProductId) {
                self.openQuickView(nextProductId);
            }
        },

        /**
         * Update the navigation buttons visibility
         */
        updateNavigation: function () {
            var $productItems = $('.product');

            if ($productItems.length <= 1) {
                this.prevBtn.hide();
                this.nextBtn.hide();
            } else {
                this.prevBtn.show();
                this.nextBtn.show();
            }
        },

        /**
         * Initialize WooCommerce scripts
         */
        initWooScripts: function () {
            // Reinitialize variation forms
            if (typeof $.fn.wc_variation_form !== 'undefined') {
                this.modal.find('.variations_form').each(function () {
                    $(this).wc_variation_form();
                });
            }

            // Reinitialize add to cart quantity buttons
            if (typeof $.fn.trigger !== 'undefined') {
                $(document.body).trigger('init_add_to_cart_quantity');
            }
        },

        /**
         * Initialize lightbox functionality
         */
        initLightbox: function () {
            if (typeof $.fn.prettyPhoto !== 'undefined') {
                this.modal.on('click', '.rmenupro-quick-view-lightbox', function (e) {
                    e.preventDefault();

                    var $this = $(this);
                    var items = [{
                        src: $this.attr('href'),
                        title: $this.data('caption') || ''
                    }];

                    $.prettyPhoto.open(items);
                    return false;
                });
            } else if (typeof $.fn.magnificPopup !== 'undefined') {
                this.modal.on('click', '.rmenupro-quick-view-lightbox', function (e) {
                    e.preventDefault();

                    var $this = $(this);
                    $.magnificPopup.open({
                        items: {
                            src: $this.attr('href')
                        },
                        type: 'image'
                    });
                    return false;
                });
            }
        },

        /**
         * Mobile-specific optimizations
         */
        mobileOptimize: function () {
            var self = this;

            // Check if we're on mobile
            if (window.matchMedia('(max-width: 768px)').matches) {
                // Adjust modal styles for mobile
                self.modal.addClass('rmenupro-quick-view-mobile');
            }

            // Handle resize events
            $(window).on('resize', function () {
                if (window.matchMedia('(max-width: 768px)').matches) {
                    self.modal.addClass('rmenupro-quick-view-mobile');
                } else {
                    self.modal.removeClass('rmenupro-quick-view-mobile');
                }
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        // Initialize the Quick View
        RMenuQuickView.init();

        // Make it globally accessible
        window.rmenuproQuickView = RMenuQuickView;
    });

})(jQuery);