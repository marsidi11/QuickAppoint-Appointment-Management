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
                'callback' => 'get_searched_appointments',
                'permission_callback' => [$this, 'current_user_can_edit_posts'],
                'args' => [
                    'search' => [
                        'required' => false,
                    ],
                    'dateFilters' => [
                        'required' => false,
                        'type' => 'array',
                    ],
                    'page' => [
                        'required' => false,
                        'default' => 1,
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

    // TODO: Show only upcoming appointments
    public function get_all_appointments(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }
        
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $services_table = $wpdb->prefix . 'am_services';
        $mapping_table = $wpdb->prefix . 'am_mapping';
    
        $page = $request->get_param('page');
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;
    
        // Get the current date and time
        $current_date = current_time('Y-m-d');
        $current_time = current_time('H:i:s');
    
        // Display only upcoming appointments
        $query = "SELECT a.*, 
                        DATE_FORMAT(a.startTime, '%H:%i') as startTime,
                        DATE_FORMAT(a.endTime, '%H:%i') as endTime,
                        GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
                        SUM(s.price) as total_price
                    FROM $appointments_table a
                    LEFT JOIN $mapping_table m ON a.id = m.appointment_id
                    LEFT JOIN $services_table s ON m.service_id = s.id
                    WHERE a.date > '$current_date' OR (a.date = '$current_date' AND a.endTime > '$current_time')
                    GROUP BY a.id
                    ORDER BY a.date ASC, a.startTime ASC
                    LIMIT $items_per_page OFFSET $offset
        ";
    
        $appointments = $wpdb->get_results($query);
    
        return new \WP_REST_Response($appointments, 200);
    }

    public function post_appointment_data(\WP_REST_Request $request)
    {
        // Validate the nonce
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        // Get the appointment data from the request
        $appointment_data = $request->get_json_params();

        // Validate the appointment data
        $required_fields = ['name', 'surname', 'phone', 'email', 'date', 'startTime', 'endTime', 'service_id'];
        foreach ($required_fields as $field) {
            if (empty($appointment_data[$field])) {
                return new \WP_Error('invalid_request', "Field '$field' is required and cannot be empty", ['status' => 400]);
            }
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $appointment_data['name'])) {
            return new \WP_Error('invalid_name', 'Name can only contain letters and whitespace', ['status' => 400]);
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $appointment_data['surname'])) {
            return new \WP_Error('invalid_surname', 'Surname can only contain letters and whitespace', ['status' => 400]);
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_data['date'])) {
            return new \WP_Error('invalid_date', 'Date must be in the format YYYY-MM-DD', ['status' => 400]);
        }

        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $appointment_data['phone'])) {
            return new \WP_Error('invalid_phone', 'Invalid phone number format', ['status' => 400]);
        }

        $start_time = strtotime($appointment_data['startTime']);
        $end_time = strtotime($appointment_data['endTime']);
        if ($start_time === false || $end_time === false || $end_time <= $start_time) {
            return new \WP_Error('invalid_time', 'Invalid start or end time', ['status' => 400]);
        }

        if (!is_array($appointment_data['service_id']) || empty($appointment_data['service_id'])) {
            return new \WP_Error('invalid_service_id', 'Service ID must be a non-empty array', ['status' => 400]);
        }

        $email = sanitize_email($appointment_data['email']);
        if (!is_email($email)) {
            return new \WP_Error('invalid_email', 'Invalid email address', ['status' => 400]);
        }

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $wpdb->query('START TRANSACTION');

        try {
            // Generate the token for email confirmation
            $token = bin2hex(openssl_random_pseudo_bytes(16));

            $result = $wpdb->insert($appointments_table, array(
                'name' => sanitize_text_field($appointment_data['name']),
                'surname' => sanitize_text_field($appointment_data['surname']),
                'phone' => sanitize_text_field($appointment_data['phone']),
                'email' => $email,
                'date' => $appointment_data['date'],
                'startTime' => $appointment_data['startTime'],
                'endTime' => $appointment_data['endTime'],
                'status' => 'Pending',
                'token' => $token
            ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));

            if ($result === false) {
                throw new Exception('Could not insert appointment into the database');
            }

            $appointment_id = $wpdb->insert_id;

            foreach ($appointment_data['service_id'] as $service_id) {
                $mapping_result = $wpdb->insert($mapping_table, array(
                    'appointment_id' => $appointment_id,
                    'service_id' => $service_id
                ), array('%d', '%d'));

                if ($mapping_result === false) {
                    throw new Exception('Could not insert appointment-service mapping into the database');
                }
            }

            // Send confirmation email
            $emailSender = new EmailSender();
            $emailSender->send_confirmation_email($email, $token);

            $wpdb->query('COMMIT');

            return new \WP_REST_Response('Appointment created successfully. Please check your email for confirmation', 201);
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new \WP_Error('db_insert_error', $e->getMessage(), ['status' => 500]);
        }
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

    public function get_searched_appointments(\WP_REST_Request $request)
    {
        error_log("get_searched_appointments function called");

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $services_table = $wpdb->prefix . 'am_services';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $search = $request->get_param('search');
        $date_filters = $request->get_param('dateFilters');
        $date_range_filter = $request->get_param('dateRange');
        $page = $request->get_param('page') ?: 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $where_clauses = [];
        $where_params = [];

        // Handle search
        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(a.name LIKE %s OR a.phone LIKE %s OR a.email LIKE %s)";
            $where_params = array_merge($where_params, [$search_term, $search_term, $search_term]);
        }

        // Handle date filters
        if (!empty($date_filters) && !is_array($date_filters)) {
            $date_filters = explode(',', $date_filters);
        }

        if (!empty($date_filters)) {
            $date_filter_clauses = [];
            foreach ($date_filters as $filter) {
                switch ($filter) {
                    case 'today':
                        $date_filter_clauses[] = "a.date = %s";
                        $where_params[] = date('Y-m-d');
                        break;
                    case 'tomorrow':
                        $date_filter_clauses[] = "a.date = %s";
                        $where_params[] = date('Y-m-d', strtotime('+1 day'));
                        break;
                    case 'upcoming':
                        $date_filter_clauses[] = "a.date >= %s";
                        $where_params[] = date('Y-m-d');
                        break;
                    case 'lastMonth':
                        $firstDayLastMonth = date('Y-m-01', strtotime('last month'));
                        $lastDayLastMonth = date('Y-m-t', strtotime('last month'));
                        $date_filter_clauses[] = "a.date BETWEEN %s AND %s";
                        $where_params[] = $firstDayLastMonth;
                        $where_params[] = $lastDayLastMonth;
                        break;
                        // Add more cases for other date filters
                }
            }
            if (!empty($date_filter_clauses)) {
                $where_clauses[] = '(' . implode(' OR ', $date_filter_clauses) . ')';
            }
        }

        // Handle date range filter
        if (!empty($date_range_filter)) {
            switch ($date_range_filter) {
                case 'nextMonth':
                    $start_date = date('Y-m-01', strtotime('+1 month'));
                    $end_date = date('Y-m-t', strtotime('+1 month'));
                    break;
                case 'previousMonth':
                    $start_date = date('Y-m-01', strtotime('-1 month'));
                    $end_date = date('Y-m-t', strtotime('-1 month'));
                    break;
                    // Add more cases for other date ranges
            }
            if (isset($start_date) && isset($end_date)) {
                $where_clauses[] = "a.date BETWEEN %s AND %s";
                $where_params[] = $start_date;
                $where_params[] = $end_date;
            }
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $query = $wpdb->prepare(
            "SELECT a.*, 
            DATE_FORMAT(a.startTime, '%%H:%%i') as startTime,
            DATE_FORMAT(a.endTime, '%%H:%%i') as endTime,
            GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
            SUM(s.price) as total_price
        FROM $appointments_table a
        LEFT JOIN $mapping_table m ON a.id = m.appointment_id
        LEFT JOIN $services_table s ON m.service_id = s.id
        $where_sql
        GROUP BY a.id
        ORDER BY a.date ASC, a.startTime ASC
        LIMIT %d OFFSET %d",
            array_merge($where_params, [$items_per_page, $offset])
        );

        error_log("Parameters: " . print_r($where_params, true));

        $appointments = $wpdb->get_results($query);

        if ($wpdb->last_error) {
            error_log("Database Error: " . $wpdb->last_error);
            return new \WP_REST_Response(['error' => $wpdb->last_error], 500);
        }

        error_log("Appointments found: " . count($appointments));

        return new \WP_REST_Response($appointments, 200);
    }

    // Helper method for permission check
    public function current_user_can_edit_posts()
    {
        return current_user_can('edit_posts');
    }
}
?>