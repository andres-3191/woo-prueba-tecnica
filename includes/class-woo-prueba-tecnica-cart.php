<?php
/**
 * Class for handling the cart functionality
 *
 * @link       https://github.com/andres-3191/woo-prueba-tecnica
 * @since      1.0.0
 *
 * @package    Woo_Prueba_Tecnica
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for handling the cart
 *
 * @since      1.0.0
 * @package    Woo_Prueba_Tecnica
 * @author     Andres Felipe Parra Ferreira <https://github.com/andres-3191>
 */
class WPT_Cart {
    /**
     * Plugin options
     *
     * @var      array    $options    Plugin options.
     */
    private $options;

    /**
     * Constructor
     *
     * @param    array    $options    Plugin options.
     */
    public function __construct( $options ) {
        $this->options = $options;
    }

    /**
     * Get the number of items in the cart
     *
     * @return   int
     */
    public function get_cart_count() {
        // Check if WooCommerce is loaded
        if ( $this->is_woocommerce_ready() ) {
            return WC()->cart->get_cart_contents_count();
        }

        return 0;
    }

    /**
     * Get the cart subtotal
     *
     * @return   string
     */
    public function get_cart_subtotal() {
        // Check if WooCommerce is loaded
        if ( $this->is_woocommerce_ready() ) {
            return WC()->cart->get_cart_subtotal();
        }

        return '0.00';
    }

    /**
     * Get cart items
     *
     * @return   array
     */
    public function get_cart_items() {
        $items = array();

        // Check if WooCommerce is loaded
        if ( ! $this->is_woocommerce_ready() ) {
            return $items;
        }

        // Get cart items
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];

            if ( ! $product ) {
                continue;
            }

            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
            $image_url = $image ? $image[0] : wc_placeholder_img_src( 'thumbnail' );

            // Add product data to array
            $items[] = array(
                'key'         => $cart_item_key,
                'product_id'  => $product_id,
                'name'        => $product->get_name(),
                'price'       => $product->get_price(),
                'price_html'  => $product->get_price_html(),
                'quantity'    => $cart_item['quantity'],
                'line_total'  => WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ),
                'image_url'   => $image_url,
                'url'         => get_permalink( $product_id ),
                'attributes'  => $this->get_item_data( $cart_item ),
                'remove_url'  => wc_get_cart_remove_url( $cart_item_key )
            );
        }

        return $items;
    }

    /**
     * Get additional data/attributes for a product
     *
     * @param    array    $cart_item
     * @return   array
     */
    private function get_item_data( $cart_item ) {
        $item_data = array();

        if ( ! empty( $cart_item['variation_id'] ) && ! empty( $cart_item['variation'] ) ) {
            foreach ( $cart_item['variation'] as $name => $value ) {
                if ( '' === $value ) {
                    continue;
                }

                $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

                // If it's a custom attribute
                if ( taxonomy_exists( $taxonomy ) ) {
                    $term = get_term_by( 'slug', $value, $taxonomy );
                    if ( ! is_wp_error( $term ) && $term && $term->name ) {
                        $value = $term->name;
                    }
                    $label = wc_attribute_label( $taxonomy );
                } else {
                    $label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
                }

                $item_data[] = array(
                    'key'   => $label,
                    'value' => $value
                );
            }
        }

        // Get product metadata
        if ( ! empty( $cart_item['data']->get_meta_data() ) ) {
            foreach ( $cart_item['data']->get_meta_data() as $meta ) {
                if ( isset( $meta->id, $meta->key, $meta->value ) ) {
                    $item_data[] = array(
                        'key'   => $meta->key,
                        'value' => $meta->value
                    );
                }
            }
        }

        return $item_data;
    }

    /**
     * Update cart item quantity
     *
     * @param    string    $cart_item_key
     * @param    int       $quantity
     * @return   bool
     */
    public function update_cart_item_quantity( $cart_item_key, $quantity ) {

        if ( ! $this->is_woocommerce_ready() ) {
            $this->log_error( 'WooCommerce cart not available' );
            return false;
        }

        $quantity = max( 0, (int) $quantity );
        $this->log_debug( "Attempting to update quantity for $cart_item_key to $quantity" );

        if ( ! isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
            $this->log_error( "Item $cart_item_key not found in cart" );
            return false;
        }

        $result = WC()->cart->set_quantity( $cart_item_key, $quantity, true );

        // Force calculation of totals
        WC()->cart->calculate_totals();

        $this->log_debug( "Update result: " . ( $result !== false ? 'success' : 'failure' ) );

        return $result !== false;
    }

    /**
     * Remove cart item
     *
     * @param    string    $cart_item_key
     * @return   bool
     */
    public function remove_cart_item( $cart_item_key ) {
        if ( ! $this->is_woocommerce_ready() ) {
            return false;
        }

        return WC()->cart->remove_cart_item( $cart_item_key );
    }

    /**
     * Get cart data for the widget (subtotal, number of items, etc)
     *
     * @return   array
     */
    public function get_cart_data() {
        return array(
            'count'         => $this->get_cart_count(),
            'subtotal'      => $this->get_cart_subtotal(),
            'items'         => $this->get_cart_items(),
            'cart_url'      => wc_get_cart_url(),
            'checkout_url'  => wc_get_checkout_url()
        );
    }

    /**
     * Check if WooCommerce is loaded and cart is available
     *
     * @return   bool
     */
    private function is_woocommerce_ready() {
        return function_exists( 'WC' ) && isset( WC()->cart );
    }

    /**
     * Log debug message
     *
     * @param    string    $message
     */
    private function log_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "WPT Cart: $message" );
        }
    }

    /**
     * Log error message
     *
     * @param    string    $message
     */
    private function log_error( $message ) {
        error_log( "WPT Cart Error: $message" );
    }
}