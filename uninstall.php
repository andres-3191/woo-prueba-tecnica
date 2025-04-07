<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link              https://github.com/andres-3191/woo-prueba-tecnica
 * @since      1.0.0
 *
 * @package    Woo_Prueba_Tecnica
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
delete_option( 'wpt_options' );
delete_site_option( 'wpt_options' );

// Delete any transients, caches, etc.
delete_transient( 'wpt_api_products' );