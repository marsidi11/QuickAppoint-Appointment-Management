<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling appointments data. 
 * Endpoints for getting and posting all appointments (ordered by date and startTime), getting a single appointment and creating a new appointment.
 * Also:
 *      Post services id and appointment id to the mapping table.
 *      Get services id and appointment id from the mapping table.
 */

 // TODO: Add validation for all the appointment data
class AppointmentsDataController extends RestController 
{
    public function register() 
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    protected function get_namespace() 
    {
        return 'appointment_management/v1';
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

        // Route to get appointments time in a specific date
        \register_rest_route($this->get_namespace(), '/' . $this->get_base() . '/reserved-time-slots', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_reserved_time_slots'),
            'permission_callback' => function () 
            {
                return true; // Allow all users to create appointments
            },
            'args' => array(
                'date' => array(
                    'required' => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                    },
                ),
            ),
        ));

    }

    // TODO: Add custom endpoints to order appointments by date, by name, booked date, etc.
    // TODO: Show only upcoming appointments
    public function get_all_appointments(\WP_REST_Request $request) 
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }
        
        // Order the appointments by date and start time, and limit the number of appointments returned
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $services_table = $wpdb->prefix . 'am_services';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $page = $request->get_param('page');
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        // Get all appointments from the database, and join service names from the mapping table
        $query = "SELECT a.*, GROUP_CONCAT(s.name SEPARATOR ', ') as service_names
            FROM $appointments_table a
            LEFT JOIN $mapping_table m ON a.id = m.appointment_id
            LEFT JOIN $services_table s ON m.service_id = s.id
            GROUP BY a.id
            ORDER BY a.date ASC, a.startTime ASC
            LIMIT $items_per_page OFFSET $offset
        ";

        $appointments = $wpdb->get_results($query);

        return new \WP_REST_Response($appointments, 200);
    }


    public function get_single_appointment(\WP_REST_Request $request) 
    {
        // Get the appointment ID from the request
        $appointment_id = $request->get_param('id');

        // Get the appointment data from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $appointment = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $appointment_id");

        // Check if the appointment exists
        if ($appointment === null) 
        {
            return new \WP_Error('not_found', 'Appointment not found', array('status' => 404));
        }

        return new \WP_REST_Response($appointment, 200);
    }
    

    public function post_appointment_data(\WP_REST_Request $request) 
    {
        if (!wp_verify_nonce($request->get_header('X_WP_Nonce'), 'wp_rest')) 
        {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        // Get the appointment data from the request
        $appointment_data = $request->get_json_params();

        // Validate the appointment data
        if (!isset($appointment_data['name']) || !isset($appointment_data['date'])) 
        {
            return new \WP_Error('invalid_request', 'Invalid appointment data', array('status' => 400));
        }

        // Check for valid name (only letters and whitespace)
        if (!preg_match("/^[a-zA-Z ]*$/", $appointment_data['name'])) 
        {
            return new \WP_Error('invalid_name', 'Name can only contain letters and whitespace', array('status' => 400));
        }

        // Check for valid date (YYYY-MM-DD format)
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_data['date'])) 
        {
            return new \WP_Error('invalid_date', 'Date must be in the format YYYY-MM-DD', array('status' => 400));
        }

        // Insert the apppointment data into the database
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $mapping_table = $wpdb->prefix . 'am_mapping';


        $result = $wpdb->insert($appointments_table, array
        (
            'name' => sanitize_text_field($appointment_data['name']),
            'surname' => sanitize_text_field($appointment_data['surname']),
            'phone' => sanitize_text_field($appointment_data['phone']),
            'email' => sanitize_email($appointment_data['email']),
            'date' => $appointment_data['date'],
            'startTime' => $appointment_data['startTime'],
            'endTime' => $appointment_data['endTime']
        ));

        if ($result === false) 
        {
            return new \WP_Error('db_insert_error', 'Could not insert appointment into the database', array('status' => 500));
        }

        // Get the ID of the inserted appointment
        $appointment_id = $wpdb->insert_id;

        // Insert the services id and appointment id into the mapping table
        foreach ($appointment_data['service_id'] as $service_id) {
            $mapping_result = $wpdb->insert(
                $mapping_table,
                array(
                    'appointment_id' => $appointment_id,
                    'service_id' => $service_id
                )
            );

            if ($mapping_result === false) {
                return new \WP_Error('db_insert_error', 'Could not insert appointment into the database', array('status' => 500));
            }
        }

        return new \WP_REST_Response('Appointment created successfully', 201);
    }

    public function get_reserved_time_slots(\WP_REST_Request $request)
    {
        // Get the date from the request
        $date = $request->get_param('date');

        // Get the reserved time slots from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $query = $wpdb->prepare("SELECT startTime, endTime FROM $table_name WHERE date = %s", $date);
        $reserved_time_slots = $wpdb->get_results($query);

        return new \WP_REST_Response($reserved_time_slots, 200);
    }
}
?>