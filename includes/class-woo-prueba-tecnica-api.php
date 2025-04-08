<?php
/**
 * Class for handling the connection with the Express API
 *
 * @link              https://github.com/andres-3191/woo-prueba-tecnica
 * @since      1.0.0
 *
 * @package    Woo_Prueba_Tecnica
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for handling API connection
 *
 * @since      1.0.0
 * @package    Woo_Prueba_Tecnica
 * @author     Andres Felipe Parra Ferreira <https://github.com/andres-3191>
 */
class WPT_API {
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
     * Make a request to the API
     *
     * @param    string       $method     HTTP method (GET, POST, ...).
     * @param    string       $endpoint   API endpoint.
     * @param    array|null   $data       Data to send (optional).
     * @return   array|WP_Error           Response or error.
     */
    private function make_request( $method, $endpoint, $data = null ) {
        $url = trailingslashit( $this->options['api_url'] ) . ltrim( $endpoint, '/' );
        $this->log_debug( 'Attempting connection ' . $method . ' to ' . $url );

        $args = array(
            'method'    => $method,
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'X-API-Key'     => $this->options['api_key'],
                'X-API-Secret'  => $this->options['api_secret']
            ),
            'timeout'   => 30,
            'sslverify' => false
        );

        if ( $data && in_array( $method, array( 'POST', 'PUT' ), true ) ) {
            $args['body'] = wp_json_encode( $data );
            $this->log_debug( 'Sending data: ' . wp_json_encode( $data ) );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log_error( $response->get_error_message() );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( $response_code >= 200 && $response_code < 300 ) {
            return json_decode( wp_remote_retrieve_body( $response ), true );
        }

        $error = new WP_Error(
            'api_error',
            sprintf(
                __( 'API Error (Code: %1$d): %2$s', 'woo-prueba-tecnica' ),
                $response_code,
                wp_remote_retrieve_body( $response )
            )
        );

        $this->log_error( $error->get_error_message() );

        return $error;
    }

    /**
     * Get products from Express service
     *
     * @return   array
     */
    public function get_products() {
        $this->log_debug( 'Starting product retrieval' );

        if ( empty( $this->options['api_url'] ) ) {
            $this->log_error( 'API URL missing' );
            return array();
        }

        try {

            $this->log_debug( 'Making request to ' . $this->options['api_url'] . '/products' );
            $response = $this->make_request( 'GET', '/products' );

            if ( is_wp_error( $response ) ) {
                $this->log_error( 'Error in request: ' . $response->get_error_message() );
                return array();
            }

            if ( ! is_array( $response ) || empty( $response ) ) {
                $this->log_error( 'Invalid response format or empty response' );
                return array();
            }

            if ( ! isset( $response[0]['name'] ) || ! isset( $response[0]['price'] ) ) {
                $this->log_error( 'Product does not have the required fields (name and price)' );
                return array();
            }

            return $response;
        } catch ( Exception $e ) {
            $this->log_error( 'Exception when getting products: ' . $e->getMessage() );
            return array();
        }
    }

    /**
     * Log debug message
     *
     * @param    string    $message
     */
    private function log_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "WPT API: $message" );
        }
    }

    /**
     * Log error message
     *
     * @param    string    $message
     */
    private function log_error( $message ) {
        error_log( "WPT API Error: $message" );
    }
}