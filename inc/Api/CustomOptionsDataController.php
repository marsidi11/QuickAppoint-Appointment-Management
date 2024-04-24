<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling custom data.
 * Custom data: Open Time, Close Time, 
 */

// TODO: Add at custom data: Available Days, Break Time, Break Duration 

class CustomOptionsDataController extends RestController 
{
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
        // Route to get open time
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/open-time', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_open_time'),
            'permission_callback' => function () 
            {
                return true; // Allow all users to get services
            }
        ));

        // Route to get close time
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/close-time', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_close_time'),
            'permission_callback' => function () 
            {
                return true; 
            }
        ));

        // Route to get dates range allowed to accept bookings
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/dates-range', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dates_range'),
            'permission_callback' => function () 
            {
                return true; 
            }
        ));

        // Route to get open days
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/open-days', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_open_days'),
            'permission_callback' => function () 
            {
                return true; 
            }
        ));
    }

    public function get_open_time(\WP_REST_Request $request)
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        return get_option('open_time', '09:00');
    }

    public function get_close_time(\WP_REST_Request $request)
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        return get_option('close_time', '17:00');
    }

    public function get_dates_range(\WP_REST_Request $request)
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        return get_option('dates_range', '21');
    }

    public function get_open_days(\WP_REST_Request $request)
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        return get_option('open_days', array());
    }
}