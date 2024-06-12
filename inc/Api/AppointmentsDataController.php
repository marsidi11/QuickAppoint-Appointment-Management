<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

use Inc\EmailConfirmation\EmailSender;
use Inc\EmailConfirmation\ConfirmationHandler;


/**
 * Custom REST API controller for handling appointments data.
 * Endpoints for managing appointments and related services.
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
        $namespace = $this->get_namespace();
        $base = $this->get_base();

        // Route definitions
        $routes = [
            [
                'route' => '',
                'methods' => 'GET',
                'callback' => 'get_all_appointments',
                'permission_callback' => [$this, 'current_user_can_edit_posts']
            ],
            [
                'route' => '/create',
                'methods' => 'POST',
                'callback' => 'post_appointment_data',
                'permission_callback' => '__return_true'
            ],
            [
                'route' => '/reserved-time-slots',
                'methods' => 'GET',
                'callback' => 'get_reserved_time_slots',
                'permission_callback' => '__return_true',
                'args' => [
                    'date' => [
                        'required' => true,
                        'validate_callback' => [$this, 'validate_date_format'],
                    ],
                ],
            ],
            [
                'route' => '/search',
                'methods' => 'GET',
                'callback' => 'search_appointment',
                'permission_callback' => [$this, 'current_user_can_edit_posts'],
                'args' => [
                    'search' => [
                        'required' => true,
                        // 'validate_callback' => 'is_string',
                    ],
                ],
            ],
        ];

        foreach ($routes as $route) {
            \register_rest_route($namespace, $base . $route['route'], [
                'methods' => $route['methods'],
                'callback' => [$this, $route['callback']],
                'permission_callback' => $route['permission_callback'],
                'args' => $route['args'] ?? []
            ]);
        }
    }

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

    public function validate_date_format($param)
    {
        return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
    }

    // TODO: Add custom endpoints to order appointments by date, by name, booked date, etc.
    // TODO: Show only upcoming appointments
    public function get_all_appointments(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
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
        $query =   "SELECT a.*, 
                        DATE_FORMAT(a.startTime, '%H:%i') as startTime,
                        DATE_FORMAT(a.endTime, '%H:%i') as endTime,
                        GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
                        SUM(s.price) as total_price
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
    
    public function post_appointment_data(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        // Get the appointment data from the request
        $appointment_data = $request->get_json_params();

        // Validate the appointment data
        $required_fields = ['name', 'date', 'startTime', 'endTime', 'service_id'];
        foreach ($required_fields as $field) {
            if (!isset($appointment_data[$field])) {
                return new \WP_Error('invalid_request', "Field '$field' is required", ['status' => 400]);
            }
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $appointment_data['name'])) {
            return new \WP_Error('invalid_name', 'Name can only contain letters and whitespace', ['status' => 400]);
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_data['date'])) {
            return new \WP_Error('invalid_date', 'Date must be in the format YYYY-MM-DD', ['status' => 400]);
        }

        // Insert the apppointment data into the database
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        // Generate the token for email confirmation
        $token = bin2hex(openssl_random_pseudo_bytes(16)); 
        $email = sanitize_email($appointment_data['email']);

        $result = $wpdb->insert($appointments_table, array
        (
            'name' => sanitize_text_field($appointment_data['name']),
            'surname' => sanitize_text_field($appointment_data['surname']),
            'phone' => sanitize_text_field($appointment_data['phone']),
            'email' => $email,
            'date' => $appointment_data['date'],
            'startTime' => $appointment_data['startTime'],
            'endTime' => $appointment_data['endTime'],
            'status' => 'Pending',
            'token' => $token
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

        // Send confirmation email
        $emailSender = new EmailSender();
        $emailSender->send_confirmation_email($email, $token);

        return new \WP_REST_Response('Appointment created successfully. Please check your email for confirmation', 201);
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

    public function search_appointment(\WP_REST_Request $request)
    {
        $search = $request->get_param('search');
        $page = $request->get_param('page');
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $search_term = '%' . $wpdb->esc_like($search) . '%';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE name LIKE %s OR phone LIKE %s OR email LIKE %s 
             LIMIT %d OFFSET %d",
            $search_term,
            $search_term,
            $search_term,
            $items_per_page,
            $offset
        );  

        $appointments = $wpdb->get_results($query);

        if ($wpdb->last_error) 
        {
            return new \WP_REST_Response(array('error' => $wpdb->last_error), 500);
        }

        return new \WP_REST_Response($appointments, 200);
    }

    // Helper method for permission check
    public function current_user_can_edit_posts()
    {
        return current_user_can('edit_posts');
    }
}
?>