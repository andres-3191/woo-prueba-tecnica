<?php
/**
 * Template for the cart widget
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
?>

<!-- Cart toggle button -->
<button class="wpt-cart-toggle">
    <span class="wpt-cart-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
    </span>
    <span class="wpt-cart-count"><?php echo esc_html( $this->cart->get_cart_count() ); ?></span>
</button>

<!-- Overlay for modal cart -->
<div class="wpt-cart-overlay"></div>

<!-- Cart container -->
<div class="wpt-cart-container wpt-display-modal">
    <div class="wpt-cart-header">
        <h2 class="wpt-cart-title"><?php esc_html_e( 'Shopping Cart', 'woo-prueba-tecnica' ); ?></h2>
        <button class="wpt-cart-close" aria-label="<?php esc_attr_e( 'Close cart', 'woo-prueba-tecnica' ); ?>">&times;</button>
    </div>

    <div class="wpt-cart-content">
        <div class="wpt-cart-items-container">
            <?php $this->render_cart_items(); ?>
        </div>
    </div>

    <?php
    // Get products from the API
    $api_products = $this->api->get_products();

    // Only show if there are valid products from the API
    if ( is_array( $api_products ) && ! empty( $api_products ) && isset( $api_products[0]['name'] ) && isset( $api_products[0]['price'] ) ) :
        $top_products = array_slice( $api_products, 0, 3 );
        ?>
        <!-- Top 3 Products Section -->
        <div class="wpt-top-products">
            <h3 class="wpt-top-products-title"><?php esc_html_e( 'Top 3 Products', 'woo-prueba-tecnica' ); ?></h3>
            <div class="wpt-top-products-grid">
                <?php foreach ( $top_products as $product ) : ?>
                    <div class="wpt-top-product">
                        <a href="<?php echo esc_url( isset( $product['url'] ) ? $product['url'] : '#' ); ?>" class="wpt-top-product-link">
                            <div class="wpt-top-product-image">
                                <img src="<?php echo esc_url( isset( $product['image'] ) ? $product['image'] : wc_placeholder_img_src() ); ?>"
                                     alt="<?php echo esc_attr( isset( $product['name'] ) ? $product['name'] : __( 'Product', 'woo-prueba-tecnica' ) ); ?>" />
                            </div>
                            <h4 class="wpt-top-product-title">
                                <?php echo esc_html( isset( $product['name'] ) ? $product['name'] : __( 'Unnamed Product', 'woo-prueba-tecnica' ) ); ?>
                            </h4>
                            <div class="wpt-top-product-price">
                                <?php echo isset( $product['price'] ) ? wc_price( $product['price'] ) : ''; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="wpt-cart-footer">
        <div>
            <span><?php esc_html_e( 'Subtotal:', 'woo-prueba-tecnica' ); ?></span>
        </div>
        <div class="wpt-cart-subtotal">
            <span class="wpt-cart-subtotal"><?php echo wp_kses_post( $this->cart->get_cart_subtotal() ); ?></span>
        </div>

        <div class="wpt-cart-actions">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="wpt-cart-button wpt-cart-button-secondary">
                <?php esc_html_e( 'View Cart', 'woo-prueba-tecnica' ); ?>
            </a>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="wpt-cart-button wpt-cart-button-primary">
                <?php esc_html_e( 'Checkout', 'woo-prueba-tecnica' ); ?>
            </a>
        </div>
    </div>
</div>