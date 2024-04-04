<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api;

class CustomDataController extends RestController 
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
        return 'bookings ';
    }

    public function register_routes() 
    {
        register_rest_route($this->get_namespace(), '/' . $this->get_base(), array(
            'methods' => 'GET',
            'callback' => array($this, 'get_custom_data'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));

        register_rest_route($this->get_namespace(), '/' . $this->get_base(), array(
            'methods' => 'POST',
            'callback' => array($this, 'post_custom_data'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));
    }

    public function get_custom_data(WP_REST_Request $request) 
    {
        // Get the booking ID from the request
        $booking_id = $request->get_param('id');

        // Get the booking data from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_bookings';
        $booking = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $booking_id");

        // Check if the booking exists
        if ($booking === null) {
            return new WP_Error('not_found', 'Booking not found', array('status' => 404));
        }

        // Return the booking data
        return new WP_REST_Response($booking, 200);
    }

    public function post_custom_data(WP_REST_Request $request) 
    {
        // Verify the nonce
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        // Get the booking data from the request
        $booking_data = $request->get_json_params();

        // Validate the booking data
        if (!isset($booking_data['name']) || !isset($booking_data['date'])) 
        {
            return new WP_Error('invalid_request', 'Invalid booking data', array('status' => 400));
        }

        // Check for valid name (only letters and whitespace)
        if (!preg_match("/^[a-zA-Z ]*$/", $booking_data['name'])) 
        {
            return new WP_Error('invalid_name', 'Name can only contain letters and whitespace', array('status' => 400));
        }

        // Check for valid date (YYYY-MM-DD format)
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $booking_data['date'])) 
        {
            return new WP_Error('invalid_date', 'Date must be in the format YYYY-MM-DD', array('status' => 400));
        }

        // Insert the booking data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_bookings';
        $wpdb->insert($table_name, array(
            'name' => sanitize_text_field($booking_data['name']),
            'date' => $booking_data['date'],
        ));

        // Check if the insert was successful
        if ($result === false) {
            return new WP_Error('db_insert_error', 'Could not insert booking into the database', array('status' => 500));
        }

        // Return a success message
        return new WP_REST_Response('Booking created successfully', 201);

    }
}
?>