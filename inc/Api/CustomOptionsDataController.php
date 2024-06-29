<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\Api;

use WP_REST_Controller;
use WP_Error;
use WP_REST_Request;

/**
 * Custom REST API controller for handling custom data.
 */
class CustomOptionsDataController extends WP_REST_Controller 
{
    const DEFAULT_OPEN_TIME = '09:00';
    const DEFAULT_CLOSE_TIME = '17:00';
    const DEFAULT_TIME_SLOT_DURATION = '30';
    const DEFAULT_DATES_RANGE = '21';
    const DEFAULT_CURRENCY_SYMBOL = '$';
    const DEFAULT_PRIMARY_COLOR = '#6b7280';
    const DEFAULT_SECONDARY_COLOR = '#1d4ed8';

    public function register()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function get_namespace()
    {
        return 'appointment_management/v1';
    }

    public function get_base()
    {
        return 'options';
    }

    public function register_routes()
    {
        $routes = [
            'open-time' => 'get_option_data',
            'close-time' => 'get_option_data',
            'time-slot-duration' => 'get_option_data',
            'dates-range' => 'get_option_data',
            'open-days' => 'get_option_data',
            'break-start' => 'get_option_data',
            'break-end' => 'get_option_data',
            'currency-symbol' => 'get_option_data',
            'primary-color' => 'get_option_data',
            'secondary-color' => 'get_option_data',
        ];

        foreach ($routes as $route => $method) {
            register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/' . $route, [
                'methods' => 'GET',
                'callback' => [$this, $method],
                'permission_callback' => '__return_true',
                'args' => [
                    'context' => [
                        'default' => 'view',
                    ],
                    'option_name' => [
                        'default' => $route,
                        'validate_callback' => 'rest_validate_request_arg',
                    ]
                ],
            ]);
        }
    }

    public function can_edit_posts($request)
    {
        return current_user_can('read');
    }

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

    public function get_option_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $option_name = str_replace('-', '_', $request['option_name']);
        $default_value = $this->get_default_value($option_name);
        $option_value = get_option($option_name, $default_value);

        if (empty($option_value) && $option_value !== '0') {
            return new WP_Error('no_value', 'No value found for ' . $option_name, ['status' => 200]);
        }

        return rest_ensure_response($option_value);
    }

    private function get_default_value($option_name)
    {
        switch ($option_name) {
            case 'open_time':
                return self::DEFAULT_OPEN_TIME;
            case 'close_time':
                return self::DEFAULT_CLOSE_TIME;
            case 'time_slot_duration':
                return self::DEFAULT_TIME_SLOT_DURATION;
            case 'dates_range':
                return self::DEFAULT_DATES_RANGE;
            case 'currency_symbol':
                return self::DEFAULT_CURRENCY_SYMBOL;
            case 'open_days':
                return [];
            case 'break_start':
            case 'break_end':
                return '';
            case 'primary_color':
                return self::DEFAULT_PRIMARY_COLOR;
            case 'secondary_color':
                return self::DEFAULT_SECONDARY_COLOR;
            default:
                return null;
        }
    }
}
