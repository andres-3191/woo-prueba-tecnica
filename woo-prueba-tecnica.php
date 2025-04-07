<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/andres-3191/woo-prueba-tecnica
 * @since             1.0.0
 * @package           Woo_Prueba_Tecnica
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Technical Test
 * Plugin URI:        https://github.com/andres-3191/woo-prueba-tecnica
 * Description:       Enhanced shopping cart widget for WooCommerce with product carousel and Express API connection.
 * Version:           1.0.0
 * Author:            Andres Felipe Parra Ferreira
 * Author URI:        https://github.com/andres-3191
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WPT_VERSION', '1.0.0' );
define( 'WPT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if WooCommerce is active
 *
 * @since    1.0.0
 * @return   bool    True if WooCommerce is active, false otherwise
 */
function wpt_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wpt_woocommerce_missing_notice' );
        return false;
    }
    return true;
}

/**
 * Error message if WooCommerce is not active
 *
 * @since    1.0.0
 * @return   void
 */
function wpt_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'WooCommerce Technical Test requires WooCommerce to be installed and activated.', 'woo-prueba-tecnica' ); ?></p>
    </div>
    <?php
}

/**
 * The code that runs during plugin activation.
 *
 * @since    1.0.0
 */
function activate_woo_prueba_tecnica() {
    // Default plugin options
    $default_options = array(
        'api_url'     => 'http://localhost:3000/api',
        'api_key'     => 'admin',
        'api_secret'  => 'admin1234'
    );

    // Add default options if they don't exist
    if ( ! get_option( 'wpt_options' ) ) {
        update_option( 'wpt_options', $default_options );
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @since    1.0.0
 */
function deactivate_woo_prueba_tecnica() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'activate_woo_prueba_tecnica' );
register_deactivation_hook( __FILE__, 'deactivate_woo_prueba_tecnica' );

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_woo_prueba_tecnica() {
    // Check if WooCommerce is active
    if ( ! wpt_check_woocommerce() ) {
        return;
    }

    // Load options
    $options = get_option( 'wpt_options' );

    // Load admin area
    if ( is_admin() ) {
        require_once WPT_PLUGIN_DIR . 'admin/class-woo-prueba-tecnica-admin.php';
        $admin = new WPT_Admin( $options );
        $admin->init();
    }

}

// Run the plugin
add_action( 'plugins_loaded', 'run_woo_prueba_tecnica' );