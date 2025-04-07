<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/andres-3191/woo-prueba-tecnica
 * @since      1.0.0
 *
 * @package    Woo_Prueba_Tecnica
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Woo_Prueba_Tecnica
 * @author     Andres Felipe Parra Ferreira <https://github.com/andres-3191>
 */
class WPT_Admin
{
    /**
     * Plugin options
     *
     * @var      array $options The plugin options.
     */
    private $options;

    /**
     * API instance
     *
     * @var      WPT_API $api The API instance.
     */
    private $api;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $options The plugin options.
     */
    public function __construct($options)
    {
        $this->options = $options;

        // Create API instance
        require_once WPT_PLUGIN_DIR . 'includes/class-woo-prueba-tecnica-api.php';
        $this->api = new WPT_API($options);
    }

    /**
     * Register the hooks for the admin area.
     */
    public function init()
    {
        add_action('admin_menu', array($this, 'add_options_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links_' . WPT_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }

    /**
     * Add options page
     */
    public function add_options_page()
    {
        add_submenu_page(
            'woocommerce',
            __('Technical Test Cart', 'woo-prueba-tecnica'),
            __('Technical Test Cart', 'woo-prueba-tecnica'),
            'manage_options',
            'woo-prueba-tecnica',
            array($this, 'render_options_page')
        );
    }

    /**
     * Add settings link on plugins page
     *
     * @param   array $links
     * @return  array
     */
    public function add_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=woo-prueba-tecnica') . '">' . __('Settings', 'woo-prueba-tecnica') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting(
            'wpt_options_group',
            'wpt_options',
            array($this, 'sanitize_options')
        );

        // API Section
        add_settings_section(
            'wpt_api',
            __('API Settings', 'woo-prueba-tecnica'),
            array($this, 'api_section_info'),
            'woo-prueba-tecnica'
        );

        add_settings_field(
            'api_url',
            __('API URL', 'woo-prueba-tecnica'),
            array($this, 'api_url_callback'),
            'woo-prueba-tecnica',
            'wpt_api'
        );

        add_settings_field(
            'api_key',
            __('API Key', 'woo-prueba-tecnica'),
            array($this, 'api_key_callback'),
            'woo-prueba-tecnica',
            'wpt_api'
        );

        add_settings_field(
            'api_secret',
            __('API Secret', 'woo-prueba-tecnica'),
            array($this, 'api_secret_callback'),
            'woo-prueba-tecnica',
            'wpt_api'
        );
    }

    /**
     * Sanitize options
     *
     * @param array $input Options input.
     * @return   array    Sanitized options.
     */
    public function sanitize_options($input)
    {
        $sanitized = array();

        // Preserve existing options
        $existing_options = get_option('wpt_options', array());

        // Sanitize API URL
        if (isset($input['api_url'])) {
            $sanitized['api_url'] = esc_url_raw($input['api_url']);
        } elseif (isset($existing_options['api_url'])) {
            $sanitized['api_url'] = $existing_options['api_url'];
        }

        // Sanitize API Key
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        } elseif (isset($existing_options['api_key'])) {
            $sanitized['api_key'] = $existing_options['api_key'];
        }

        // Sanitize API Secret
        if (isset($input['api_secret'])) {
            $sanitized['api_secret'] = sanitize_text_field($input['api_secret']);
        } elseif (isset($existing_options['api_secret'])) {
            $sanitized['api_secret'] = $existing_options['api_secret'];
        }

        return $sanitized;
    }

    /**
     * Render options page
     */
    public function render_options_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('wpt_options_group');
                do_settings_sections('woo-prueba-tecnica');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * API section info
     */
    public function api_section_info()
    {
        echo '<p>' . esc_html__('Configure the connection parameters for the Express API service.', 'woo-prueba-tecnica') . '</p>';
    }

    /**
     * API URL callback
     */
    public function api_url_callback()
    {
        printf(
            '<input type="text" id="api_url" name="wpt_options[api_url]" value="%s" class="regular-text" />',
            isset($this->options['api_url']) ? esc_attr($this->options['api_url']) : ''
        );
        echo '<p class="description">' . esc_html__('URL of the Express API service (example: http://localhost:3000/api)', 'woo-prueba-tecnica') . '</p>';
    }

    /**
     * API Key callback
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="wpt_options[api_key]" value="%s" class="regular-text" />',
            isset($this->options['api_key']) ? esc_attr($this->options['api_key']) : ''
        );
        echo '<p class="description">' . esc_html__('API Key for authentication', 'woo-prueba-tecnica') . '</p>';
    }

    /**
     * API Secret callback
     */
    public function api_secret_callback()
    {
        printf(
            '<input type="password" id="api_secret" name="wpt_options[api_secret]" value="%s" class="regular-text" />',
            isset($this->options['api_secret']) ? esc_attr($this->options['api_secret']) : ''
        );
        echo '<p class="description">' . esc_html__('API Secret for authentication', 'woo-prueba-tecnica') . '</p>';
    }
}