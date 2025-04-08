/**
 * Public JavaScript for the WooCommerce Technical Test plugin.
 *
 * @link       https://github.com/andres-3191/woo-prueba-tecnica
 * @since      1.0.0
 *
 * @package    Woo_Prueba_Tecnica
 */

(function($) {
    'use strict';

    // Cart functionality object
    const WPTCart = {
        init: function() {
            this.bindEvents();
            this.initCartWidget();
        },

        bindEvents: function() {
            $(document).on('click', '.wpt-cart-toggle', this.toggleCart);

            $(document).on('click', function(e) {
                if (
                    $('.wpt-cart-container').hasClass('open') &&
                    !$(e.target).closest('.wpt-cart-container').length &&
                    !$(e.target).closest('.wpt-cart-toggle').length
                ) {
                    WPTCart.closeCart();
                }
            });

            // Buttons
            $(document).on('click', '.wpt-cart-close', this.closeCart);
            $(document).on('click', '.wpt-qty-increase', this.increaseQuantity);
            $(document).on('click', '.wpt-qty-decrease', this.decreaseQuantity);
            $(document).on('change', '.wpt-qty-input', this.quantityChanged);
            $(document).on('click', '.wpt-cart-remove-item', this.removeItem);

            // Listen for WooCommerce fragment refresh events
            $(document.body).on('added_to_cart removed_from_cart wc_fragments_refreshed wc_fragments_loaded', function() {
                WPTCart.refreshCart();
            });

            // Refresh cart when toggle button is clicked
            $(document).on('click', '.wpt-cart-toggle', function() {
                WPTCart.refreshCart();
            });
        },

        // Initialize the cart widget based on display type
        initCartWidget: function() {
            $('.wpt-cart-container').addClass('wpt-display-modal');

            // Add notification badge if items in cart
            const count = parseInt($('.wpt-cart-count').text(), 10);
            if (count > 0) {
                $('.wpt-cart-toggle').addClass('has-items');
            }
        },

        toggleCart: function(e) {
            e.preventDefault();
            $('.wpt-cart-container').toggleClass('open');
            $('.wpt-cart-overlay').toggleClass('active');
        },

        closeCart: function(e) {
            if (e) {
                e.preventDefault();
            }
            $('.wpt-cart-container').removeClass('open');
            $('.wpt-cart-overlay').removeClass('active');
        },

        increaseQuantity: function(e) {
            e.preventDefault();
            const $input = $(this).siblings('.wpt-qty-input');
            const currentValue = parseInt($input.val(), 10);
            $input.val(currentValue + 1).trigger('change');
        },

        decreaseQuantity: function(e) {
            e.preventDefault();
            const $input = $(this).siblings('.wpt-qty-input');
            const currentValue = parseInt($input.val(), 10);
            if (currentValue > 0) {
                $input.val(currentValue - 1).trigger('change');
            }
        },

        quantityChanged: function() {
            const $item = $(this).closest('.wpt-cart-item');
            const key = $item.data('key');
            const quantity = parseInt($(this).val(), 10);

            $(this).data('original-value', $(this).val());
            $item.addClass('loading');

            $.ajax({
                url: wpt_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpt_update_quantity',
                    nonce: wpt_params.nonce,
                    key: key,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {

                        WPTCart.updateFragments(response.data.fragments);

                        if (quantity === 0) {
                            $item.slideUp(300, function() {
                                $(this).remove();

                                if ($('.wpt-cart-item').length === 0) {
                                    $('.wpt-cart-items-container').html('<div class="wpt-empty-cart">' + wpt_params.empty_cart_msg + '</div>');
                                }
                            });
                        }
                    } else {
                        alert(response.data.message || 'Error updating cart');
                    }

                    $item.removeClass('loading');
                },
                error: function() {

                    alert('Error connecting to server');
                    $item.removeClass('loading');
                    $(this).val($(this).data('original-value'));

                }
            });
        },

        removeItem: function(e) {
            e.preventDefault();
            const $item = $(this).closest('.wpt-cart-item');
            const key = $(this).data('key');

            $item.addClass('loading');

            $.ajax({
                url: wpt_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpt_remove_item',
                    nonce: wpt_params.nonce,
                    key: key
                },
                success: function(response) {
                    if (response.success) {
                        WPTCart.updateFragments(response.data.fragments);

                        $item.slideUp(300, function() {
                            $(this).remove();

                            if ($('.wpt-cart-item').length === 0) {
                                $('.wpt-cart-items-container').html('<div class="wpt-empty-cart">' + wpt_params.empty_cart_msg + '</div>');
                            }
                        });
                    } else {

                        alert(response.data.message || 'Error removing item');
                        $item.removeClass('loading');

                    }
                },
                error: function() {

                    alert('Error connecting to server');
                    $item.removeClass('loading');
                }
            });
        },

        refreshCart: function() {
            $.ajax({
                url: wpt_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpt_get_cart',
                    nonce: wpt_params.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {

                        $('.wpt-cart-count').text(response.data.count);
                        $('.wpt-cart-subtotal').html(response.data.subtotal);

                        if ($('.wpt-cart-container').hasClass('open')) {
                            WPTCart.renderCartItems(response.data.items);
                        }

                        const count = parseInt(response.data.count, 10);
                        if (count > 0) {
                            $('.wpt-cart-toggle').addClass('has-items');
                        } else {
                            $('.wpt-cart-toggle').removeClass('has-items');
                        }
                    }
                }
            });
        },

        renderCartItems: function(items) {
            if (!items || items.length === 0) {
                $('.wpt-cart-items-container').html('<div class="wpt-empty-cart">' + wpt_params.empty_cart_msg + '</div>');
                return;
            }

            let html = '<ul class="wpt-cart-items">';

            $.each(items, function(index, item) {
                html += '<li class="wpt-cart-item" data-key="' + item.key + '">';
                html += '<div class="wpt-cart-item-image">';
                html += '<a href="' + item.url + '">';
                html += '<img src="' + item.image_url + '" alt="' + item.name + '" />';
                html += '</a>';
                html += '</div>';
                html += '<div class="wpt-cart-item-details">';
                html += '<h4 class="wpt-cart-item-title">';
                html += '<a href="' + item.url + '">' + item.name + '</a>';
                html += '</h4>';

                if (item.attributes && item.attributes.length > 0) {
                    html += '<div class="wpt-cart-item-attributes">';
                    $.each(item.attributes, function(i, attr) {
                        html += '<span class="wpt-cart-item-attribute">';
                        html += attr.key + ': ' + attr.value;
                        html += '</span>';
                    });
                    html += '</div>';
                }

                html += '<div class="wpt-cart-item-price">' + item.price_html + '</div>';
                html += '</div>';
                html += '<div class="wpt-cart-item-quantity">';
                html += '<div class="wpt-quantity-controls">';
                html += '<button class="wpt-qty-decrease" aria-label="' + wpt_params.i18n_quantity + '">-</button>';
                html += '<input type="number" class="wpt-qty-input" value="' + item.quantity + '" min="0" max="99" step="1" />';
                html += '<button class="wpt-qty-increase" aria-label="' + wpt_params.i18n_quantity + '">+</button>';
                html += '</div>';
                html += '</div>';
                html += '<div class="wpt-cart-item-subtotal">' + item.line_total + '</div>';
                html += '<a href="#" class="wpt-cart-remove-item" data-key="' + item.key + '" aria-label="' + wpt_params.i18n_remove_item + '">&times;</a>';
                html += '</li>';
            });

            html += '</ul>';
            $('.wpt-cart-items-container').html(html);
        },

        updateFragments: function(fragments) {
            if (fragments) {
                $.each(fragments, function(selector, content) {
                    $(selector).replaceWith(content);
                });
            }

            const count = parseInt($('.wpt-cart-count').text(), 10);
            if (count > 0) {
                $('.wpt-cart-toggle').addClass('has-items');
            } else {
                $('.wpt-cart-toggle').removeClass('has-items');
            }
        }
    };

    $(document).ready(function() {
        WPTCart.init();
    });

})(jQuery);