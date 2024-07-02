<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;
use \Inc\Api\Callbacks\ColorGenerator;

/**
 * Enqueue scripts and styles 
 */
class Enqueue extends BaseController 
{
    const BACKEND_STYLE_HANDLE = 'appointment-management-backend-style';
    const BACKEND_SCRIPT_HANDLE = 'appointment-management-backend-script';
    const FRONTEND_STYLE_HANDLE = 'appointment-management-frontend-style';
    const FRONTEND_SCRIPT_HANDLE = 'appointment-management-frontend-script';
    const COLOR_STYLE_HANDLE = 'appointment-management-color-style';

    const BACKEND_STYLE_PATH = 'assets/dist/backend.styles.css';
    const BACKEND_SCRIPT_PATH = 'assets/dist/backend.bundle.js';
    const FRONTEND_STYLE_PATH = 'assets/dist/frontend.styles.css';
    const FRONTEND_SCRIPT_PATH = 'assets/dist/frontend.bundle.js';
    
    const COLOR_OPTION_KEY = 'appointment_management_color_css';

    public function register() 
    {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts' ) ); // Enqueue scripts for admin-side
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) ); // Enqueue scripts for front-end

        add_action('wp_head', array($this, 'print_color_styles'), 1);
        add_action('admin_head', array($this, 'print_color_styles'), 1);
        add_action('update_option_background_color', array($this, 'update_color_cache'));
        add_action('update_option_primary_color', array($this, 'update_color_cache'));
        add_action('update_option_secondary_color', array($this, 'update_color_cache'));
        add_action('update_option_text_color', array($this, 'update_color_cache'));
    }

    private function enqueue_scripts($style_handle, $script_handle, $style_path, $script_path) 
    {
        wp_enqueue_style( $style_handle, $this->plugin_url . $style_path );
        wp_enqueue_script($script_handle, $this->plugin_url . $script_path, array(), null, true);

        // Create a nonce
        $nonce = wp_create_nonce('wp_rest');

        // Localize the script with your data.
        $data = array(
            'nonce' => $nonce,
            'apiUrlAppointments' => esc_url_raw( rest_url('appointment_management/v1/appointments') ),
            'apiUrlServices' => esc_url_raw( rest_url('appointment_management/v1/services') ),
            'apiUrlOptions' => esc_url_raw( rest_url('appointment_management/v1/options') ),
        );

        wp_localize_script($script_handle, 'wpApiSettings', $data);
    }

    public function enqueue_backend_scripts() 
    {
        $this->enqueue_scripts(
            self::BACKEND_STYLE_HANDLE, 
            self::BACKEND_SCRIPT_HANDLE, 
            self::BACKEND_STYLE_PATH, 
            self::BACKEND_SCRIPT_PATH
        );
    }

    public function enqueue_frontend_scripts() 
    {
        $this->enqueue_scripts(
            self::FRONTEND_STYLE_HANDLE, 
            self::FRONTEND_SCRIPT_HANDLE, 
            self::FRONTEND_STYLE_PATH, 
            self::FRONTEND_SCRIPT_PATH
        );
    }

    public function print_color_styles()
    {
        $css = $this->get_color_css();
        if (!empty($css)) {
            echo "<style id='appointment-management-color-style'>\n" . $css . "\n</style>\n";
        }
    }

    private function get_color_css() 
    {
        $css = get_option(self::COLOR_OPTION_KEY);
        if (false === $css) {
            $css = ColorGenerator::generate_color_variables();
            update_option(self::COLOR_OPTION_KEY, $css);
        }
        return $css;
    }

    public function update_color_cache() 
    {
        $css = ColorGenerator::generate_color_variables();
        update_option(self::COLOR_OPTION_KEY, $css);
    }
}
