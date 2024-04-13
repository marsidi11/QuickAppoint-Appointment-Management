<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling custom data. 
 * Endpoints for getting all services and creating new services.
 */

class ServicesDataController extends RestController 
{
    public function register() 
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    protected function get_namespace() 
    {
        return 'booking-management/v1';
    }

    protected function get_base() 
    {
        return 'services';
    }

    public function register_routes() 
    {

        // Route to get all services
        \register_rest_route($this->get_namespace(), '/' . $this->get_base(), array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_services'),
            'permission_callback' => function () 
            {
                return true; // Allow all users to get services
            }
        ));

        // Route to create new services
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'post_service_data'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));

    }

    public function get_all_services(\WP_REST_Request $request) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $query = "SELECT * FROM $table_name";

        $bookings = $wpdb->get_results($query);

        return new \WP_REST_Response($bookings, 200);
    }

    public function post_service_data(\WP_REST_Request $request) 
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        $booking_data = $request->get_json_params();

        // Validate the appointment data
        if (!isset($booking_data['name']) || !isset($booking_data['price']) || !isset($booking_data['duration'])) 
        {
            return new WP_Error('invalid_request', 'Invalid booking data', array('status' => 400));
        }

        // Insert the services data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $result = $wpdb->insert($table_name, array
        (
            'name' => sanitize_text_field($booking_data['name']),

            'description' => sanitize_text_field($booking_data['description']),

            'duration' => $booking_data['duration'],

            'price' => $booking_data['price'],
        ));

        if ($result === false) 
        {
            return new \WP_Error('db_insert_error', 'Could not insert services into the database', array('status' => 500));
        }

        return new \WP_REST_Response('Service created successfully', 201);
        
    }

}