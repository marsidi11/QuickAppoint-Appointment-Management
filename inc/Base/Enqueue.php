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
    // Use a unique prefix for all constants and handles to avoid conflicts
    const PREFIX = 'am_plugin_';
    const BACKEND_STYLE_HANDLE = self::PREFIX . 'backend_style';
    const BACKEND_SCRIPT_HANDLE = self::PREFIX . 'backend_script';
    const FRONTEND_STYLE_HANDLE = self::PREFIX . 'frontend_style';
    const FRONTEND_SCRIPT_HANDLE = self::PREFIX . 'frontend_script';
    const COLOR_STYLE_HANDLE = self::PREFIX . 'color_style';

    const BACKEND_STYLE_PATH = 'assets/dist/backend.styles.css';
    const BACKEND_SCRIPT_PATH = 'assets/dist/backend.bundle.js';
    const FRONTEND_STYLE_PATH = 'assets/dist/frontend.styles.css';
    const FRONTEND_SCRIPT_PATH = 'assets/dist/frontend.bundle.js';
    
    const COLOR_OPTION_KEY = self::PREFIX . 'color_css';

    public function register() 
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_backend_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'conditionally_enqueue_frontend_scripts']);

        add_action('wp_head', [$this, 'print_color_styles'], 100); // Lower priority to load after theme styles
        add_action('admin_head', [$this, 'print_color_styles'], 100);
        
        // Use a single action for all color option updates
        add_action('update_option', [$this, 'maybe_update_color_cache'], 10, 3);
    }

    private function enqueue_scripts($style_handle, $script_handle, $style_path, $script_path) 
    {
        $version = $this->get_plugin_version();
        
        wp_enqueue_style($style_handle, $this->plugin_url . $style_path, [], $version);
        wp_enqueue_script($script_handle, $this->plugin_url . $script_path, ['jquery'], $version, true);

        wp_localize_script($script_handle, self::PREFIX . 'api_settings', [
            'nonce' => wp_create_nonce('wp_rest'),
            'apiUrlAppointments' => esc_url_raw(rest_url('appointment_management/v1/appointments')),
            'apiUrlServices' => esc_url_raw(rest_url('appointment_management/v1/services')),
            'apiUrlOptions' => esc_url_raw(rest_url('appointment_management/v1/options')),
        ]);
    }

    public function enqueue_backend_scripts($hook_suffix)
    {
        $allowed_pages = [
            'appointment_page_appointment_management_settings',
            'toplevel_page_appointment_management'
        ];
        
        if (in_array($hook_suffix, $allowed_pages)) {
            $this->enqueue_scripts(
                self::BACKEND_STYLE_HANDLE,
                self::BACKEND_SCRIPT_HANDLE,
                self::BACKEND_STYLE_PATH,
                self::BACKEND_SCRIPT_PATH
            );
        }
    }

    public function conditionally_enqueue_frontend_scripts()
    {
        if (is_singular() && has_shortcode(get_post()->post_content, 'am_form') || 
            has_shortcode(get_post()->post_content, 'am_confirmation')) {
            $this->enqueue_frontend_scripts();
        }
    }

    private function enqueue_frontend_scripts() 
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
            printf(
                "<style id='%s'>\n%s\n</style>\n",
                esc_attr(self::COLOR_STYLE_HANDLE),
                wp_strip_all_tags($css)
            );
        }
    }

    private function get_color_css() 
    {
        $css = get_option(self::COLOR_OPTION_KEY);
        if (false === $css) {
            $css = $this->generate_and_cache_color_css();
        }
        return $css;
    }

    private function generate_and_cache_color_css()
    {
        $css = ColorGenerator::generate_color_variables();
        update_option(self::COLOR_OPTION_KEY, $css);
        return $css;
    }

    public function maybe_update_color_cache($option_name, $old_value, $new_value) 
    {
        $color_options = ['background_color', 'primary_color', 'secondary_color', 'text_color'];
        if (in_array($option_name, $color_options) && $old_value !== $new_value) {
            $this->generate_and_cache_color_css();
        }
    }

    private function get_plugin_version()
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data($this->plugin_path . 'quickappoint.php');
        return $plugin_data['Version'];
    }
}