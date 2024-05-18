<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling custom data.
 * Custom data: Open Time, Close Time, 
 */


class CustomOptionsDataController extends RestController 
{
    const DEFAULT_OPEN_TIME = '09:00';
    const DEFAULT_CLOSE_TIME = '17:00';
    const DEFAULT_TIME_SLOT_DURATION = '30';
    const DEFAULT_DATES_RANGE = '21';
    const DEFAULT_CURRENCY_SYMBOL = '$';

    public function register()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
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
        $permission_callback = function () {
            return true; // Allow all users to get custom options data
        };

        $routes = [
            'open-time' => 'get_open_time',
            'close-time' => 'get_close_time',
            'time-slot-duration' => 'get_time_slot_duration',
            'dates-range' => 'get_dates_range',
            'open-days' => 'get_open_days',
            'break-start' => 'get_break_start',
            'break-end' => 'get_break_end',
            'currency-symbol' => 'get_currency_symbol',
        ];

        foreach ($routes as $route => $method) {
            \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/' . $route, array(
                'methods' => 'GET',
                'callback' => array($this, $method),
                'permission_callback' => $permission_callback,
                'args' => [
                    'context' => [
                        'default' => 'view',
                    ],
                ],
            ));
        }
    }

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }
        return true;
    }

    public function get_open_time(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('open_time', self::DEFAULT_OPEN_TIME);
    }

    public function get_close_time(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('close_time', self::DEFAULT_CLOSE_TIME);
    }

    public function get_time_slot_duration(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('time_slot_duration', self::DEFAULT_TIME_SLOT_DURATION);
    }

    public function get_dates_range(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('dates_range', self::DEFAULT_DATES_RANGE);
    }

    public function get_open_days(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('open_days', array());
    }

    public function get_break_start(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $break_start = get_option('break_start');

        if (empty($break_start)) {
            return new \WP_Error('no_value', 'No value for Break Start', array('status' => 200));
        }

        return $break_start;
    }

    public function get_break_end(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $break_end = get_option('break_end');

        if (empty($break_end)) {
            return new \WP_Error('no_value', 'No value for Break End', array('status' => 200));
        }

        return $break_end;
    }

    public function get_currency_symbol(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        return get_option('currency_symbol', self::DEFAULT_CURRENCY_SYMBOL);
    }
}