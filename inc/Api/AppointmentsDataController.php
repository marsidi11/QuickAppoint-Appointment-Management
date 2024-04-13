<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling appointments data. 
 * Endpoints for getting and posting all appointments (ordered by date and startTime), getting a single appointment and creating a new appointment.
 */

class AppointmentsDataController extends RestController 
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
        return 'appointments';
    }

    public function register_routes() 
    {

        // Route to get all appointments
        \register_rest_route($this->get_namespace(), '/' . $this->get_base(), array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_appointments'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));

        // Route to get a single appointment
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_single_appointment'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));

        // Route to create a new appointment
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'post_appointment_data'),
            'permission_callback' => function () 
            {
                return true; // Allow all users to create appointments
            }
        ));

    }

    // TODO: Add custom endpoints to order bookings by date, by name, booked date, etc.
    // TODO: Show only upcoming appointments
    public function get_all_appointments(\WP_REST_Request $request) 
    {
        // Order the appointments by date and start time, and limit the number of appointments returned
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_bookings';

        $page = $request->get_param('page');
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $query = "SELECT * FROM $table_name ORDER BY date ASC, startTime ASC LIMIT $items_per_page OFFSET $offset";

        $bookings = $wpdb->get_results($query);

        return new \WP_REST_Response($bookings, 200);
    }


    public function get_single_appointment(\WP_REST_Request $request) 
    {
        // Get the booking ID from the request
        $booking_id = $request->get_param('id');

        // Get the booking data from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_bookings';
        $booking = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $booking_id");

        // Check if the booking exists
        if ($booking === null) 
        {
            return new \WP_Error('not_found', 'Booking not found', array('status' => 404));
        }

        return new \WP_REST_Response($booking, 200);
    }
    

    public function post_appointment_data(\WP_REST_Request $request) 
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        // Get the booking data from the request
        $booking_data = $request->get_json_params();

        // Validate the appointment data
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

        // TODO: Update the code to post services to the table
        // Insert the apppointment data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_bookings';

        $result = $wpdb->insert($table_name, array
        (
            'name' => sanitize_text_field($booking_data['name']),

            'surname' => sanitize_text_field($booking_data['surname']),

            'phone' => sanitize_text_field($booking_data['phone']),

            'email' => sanitize_email($booking_data['email']),

            'service_id' => $booking_data['service_id'],

            'date' => $booking_data['date'],

            'startTime' => $booking_data['startTime'],

            'endTime' => $booking_data['endTime']
        ));

        if ($result === false) 
        {
            return new \WP_Error('db_insert_error', 'Could not insert appointment into the database', array('status' => 500));
        }

        return new \WP_REST_Response('Appointment created successfully', 201);

    }
}
?>