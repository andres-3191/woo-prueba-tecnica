<?php
/**
 * The public-facing functionality of the plugin.
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
 * The public-facing functionality of the plugin.
 *
 * @package    Woo_Prueba_Tecnica
 * @author     Andres Felipe Parra Ferreira <https://github.com/andres-3191>
 */
class WPT_Public {

    /**
     * Plugin options
     *
     * @var      array    $options    The plugin options.
     */
    private $options;

    /**
     * API instance
     *
     * @var      WPT_API    $api    The API instance.
     */
    private $api;

    /**
     * Cart instance
     *
     * @var      WPT_Cart    $cart    The cart instance.
     */
    private $cart;

    /**
     * Initialize the class and set its properties.
     *
     * @param    array    $options    The plugin options.
     */
    public function __construct( $options ) {
        $this->options = $options;

        // Create API instance
        require_once WPT_PLUGIN_DIR . 'includes/class-woo-prueba-tecnica-api.php';
        $this->api = new WPT_API( $options );

        // Create Cart instance
        require_once WPT_PLUGIN_DIR . 'includes/class-woo-prueba-tecnica-cart.php';
        $this->cart = new WPT_Cart( $options );
    }

    /**
     * Register the hooks for the public area.
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
        add_action( 'wp_footer', array( $this, 'cart_widget' ), 999 );
        add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ), 999 );
        add_action( 'wp_ajax_wpt_get_cart', array( $this, 'ajax_get_cart' ) );
        add_action( 'wp_ajax_nopriv_wpt_get_cart', array( $this, 'ajax_get_cart' ) );
        add_action( 'wp_ajax_wpt_update_quantity', array( $this, 'ajax_update_quantity' ) );
        add_action( 'wp_ajax_nopriv_wpt_update_quantity', array( $this, 'ajax_update_quantity' ) );
        add_action( 'wp_ajax_wpt_remove_item', array( $this, 'ajax_remove_item' ) );
        add_action( 'wp_ajax_nopriv_wpt_remove_item', array( $this, 'ajax_remove_item' ) );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        // Don't load on checkout or cart page
        if ( is_checkout() || is_cart()) {
            return;
        }

        wp_enqueue_style(
            'wpt-public',
            WPT_PLUGIN_URL . 'public/css/woo-prueba-tecnica-public.css',
            array(),
            WPT_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        // Don't load on checkout page
        if ( is_checkout() || is_cart() ) {
            return;
        }

        wp_enqueue_script(
            'wpt-public',
            WPT_PLUGIN_URL . 'public/js/woo-prueba-tecnica-public.js',
            array( 'jquery' ),
            WPT_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'wpt-public',
            'wpt_params',
            array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'wpt_nonce' ),
                'cart_url'          => wc_get_cart_url(),
                'checkout_url'      => wc_get_checkout_url(),
                'empty_cart_msg'    => __( 'Your cart is currently empty.', 'woo-prueba-tecnica' ),
                'i18n_remove_item'  => __( 'Remove this item', 'woo-prueba-tecnica' ),
                'i18n_quantity'     => __( 'Quantity', 'woo-prueba-tecnica' ),
            )
        );
    }

    /**
     * Add cart fragments for AJAX cart updates
     *
     * @param    array    $fragments
     * @return   array
     */
    public function cart_fragments( $fragments ) {
        // Ensure the cart is calculated before generating fragments
        if (function_exists('WC') && isset(WC()->cart)) {
            WC()->cart->calculate_totals();
        }

        // Update cart count
        $fragments['.wpt-cart-count'] = '<span class="wpt-cart-count">' . $this->cart->get_cart_count() . '</span>';

        // Update cart subtotal
        $fragments['.wpt-cart-subtotal'] = '<span class="wpt-cart-subtotal">' . $this->cart->get_cart_subtotal() . '</span>';

        // Update cart items
        ob_start();
        $this->render_cart_items();
        $cart_items_html = ob_get_clean();
        $fragments['.wpt-cart-items-container'] = '<div class="wpt-cart-items-container">' . $cart_items_html . '</div>';

        // Debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WPT Cart Fragments: Items updated - Count: ' . $this->cart->get_cart_count());
        }

        return $fragments;
    }

    /**
     * Output the cart widget
     */
    public function cart_widget() {
        // Don't show on checkout or cart page
        if ( is_checkout() || is_cart() ) {
            return;
        }

        // Make sure cart is calculated before displaying widget
        if (function_exists('WC') && isset(WC()->cart)) {
            WC()->cart->calculate_totals();
        }

        include WPT_PLUGIN_DIR . 'public/widgets/cart-widget.php';
    }

    /**
     * Render cart items for the widget
     */
    public function render_cart_items() {
        $items = $this->cart->get_cart_items();

        if ( empty( $items ) ) {
            echo '<div class="wpt-empty-cart">' . esc_html__( 'Your cart is currently empty.', 'woo-prueba-tecnica' ) . '</div>';
            return;
        }

        echo '<ul class="wpt-cart-items">';

        foreach ( $items as $item ) {
            ?>
            <li class="wpt-cart-item" data-key="<?php echo esc_attr( $item['key'] ); ?>">
                <div class="wpt-cart-item-image">
                    <a href="<?php echo esc_url( $item['url'] ); ?>">
                        <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" />
                    </a>
                </div>
                <div class="wpt-cart-item-details">
                    <h4 class="wpt-cart-item-title">
                        <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a>
                    </h4>

                    <?php if ( ! empty( $item['attributes'] ) ) : ?>
                        <div class="wpt-cart-item-attributes">
                            <?php foreach ( $item['attributes'] as $attribute ) : ?>
                                <span class="wpt-cart-item-attribute">
                                    <?php echo esc_html( $attribute['key'] ); ?>: <?php echo esc_html( $attribute['value'] ); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="wpt-cart-item-price">
                        <?php echo wp_kses_post( $item['price_html'] ); ?>
                    </div>
                </div>
                <div class="wpt-cart-item-quantity">
                    <div class="wpt-quantity-controls">
                        <button class="wpt-qty-decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'woo-prueba-tecnica' ); ?>">-</button>
                        <input type="number" class="wpt-qty-input" value="<?php echo esc_attr( $item['quantity'] ); ?>" min="0" max="99" step="1" />
                        <button class="wpt-qty-increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'woo-prueba-tecnica' ); ?>">+</button>
                    </div>
                </div>
                <div class="wpt-cart-item-subtotal">
                    <?php echo wp_kses_post( $item['line_total'] ); ?>
                </div>
                <a href="#" class="wpt-cart-remove-item" data-key="<?php echo esc_attr( $item['key'] ); ?>" aria-label="<?php esc_attr_e( 'Remove this item', 'woo-prueba-tecnica' ); ?>">&times;</a>
            </li>
            <?php
        }

        echo '</ul>';
    }

    /**
     * AJAX endpoint to get cart data
     */
    public function ajax_get_cart() {
        check_ajax_referer( 'wpt_nonce', 'nonce' );

        // Make sure cart is up to date
        if (function_exists('WC') && isset(WC()->cart)) {
            WC()->cart->calculate_totals();
        }

        $cart_data = $this->cart->get_cart_data();
        wp_send_json_success( $cart_data );
    }

    /**
     * AJAX endpoint to update cart item quantity
     */
    public function ajax_update_quantity() {
        check_ajax_referer( 'wpt_nonce', 'nonce' );
        $cart_item_key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
        $quantity = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 0;

        if ( empty( $cart_item_key ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid cart item key.', 'woo-prueba-tecnica' ) ) );
        }

        $updated = $this->cart->update_cart_item_quantity( $cart_item_key, $quantity );

        if ( $updated ) {
            $cart_data = $this->cart->get_cart_data();
            $fragments = $this->cart_fragments( array() );

            wp_send_json_success( array(
                'cart_data'  => $cart_data,
                'fragments'  => $fragments,
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to update quantity.', 'woo-prueba-tecnica' ) ) );
        }
    }

    /**
     * AJAX endpoint to remove cart item
     */
    public function ajax_remove_item() {
        check_ajax_referer( 'wpt_nonce', 'nonce' );
        $cart_item_key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

        if ( empty( $cart_item_key ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid cart item key.', 'woo-prueba-tecnica' ) ) );
        }

        $removed = $this->cart->remove_cart_item( $cart_item_key );

        if ( $removed ) {
            $cart_data = $this->cart->get_cart_data();
            $fragments = $this->cart_fragments( array() );

            wp_send_json_success( array(
                'cart_data'  => $cart_data,
                'fragments'  => $fragments,
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to remove item.', 'woo-prueba-tecnica' ) ) );
        }
    }
}