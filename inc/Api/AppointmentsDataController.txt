<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

use Inc\EmailConfirmation\EmailSender;

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
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/delete/(?P<id>\d+)',
                'methods' => 'DELETE',
                'callback' => 'delete_appointment_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/create',
                'methods' => 'POST',
                'callback' => 'post_appointment_data',
                'permission_callback' => '__return_true'
            ],
            [
                'route' => '/update/(?P<id>\d+)',
                'methods' => 'UPDATE',
                'callback' => 'update_appointment_data',
                'permission_callback' => [$this, 'can_edit_posts']
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

    // Helper method for permission check
    public function can_edit_posts()
    {
        return current_user_can('edit_posts');
    }

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

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
                throw new \Exception('Could not insert appointment into the database');
            }

            $appointment_id = $wpdb->insert_id;

            foreach ($appointment_data['service_id'] as $service_id) {
                $mapping_result = $wpdb->insert($mapping_table, array(
                    'appointment_id' => $appointment_id,
                    'service_id' => $service_id
                ), array('%d', '%d'));

                if ($mapping_result === false) {
                    throw new \Exception('Could not insert appointment-service mapping into the database');
                }
            }

            // Get the admin email address
            $admin_email = get_option('admin_email');

            // Send confirmation email to user
            $emailSender = new EmailSender();
            $emailSender->send_confirmation_email_to_user($email, $token);

            // Notify admin about the new appointment
            $emailSender->notify_admin_about_appointment($admin_email, $appointment_data, $token);

            $wpdb->query('COMMIT');

            return new \WP_REST_Response('Appointment created successfully. Please check your email for confirmation', 201);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new \WP_Error('db_insert_error', $e->getMessage(), ['status' => 500]);
        }
    }

    public function delete_appointment_data(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_id = $request['id'];

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $wpdb->query('START TRANSACTION');

        try {
            $result = $wpdb->delete($appointments_table, array('id' => $appointment_id));
            if ($result === false) {
                throw new \Exception('Could not delete appointment from the database');
            }

            $mapping_result = $wpdb->delete($mapping_table, array('appointment_id' => $appointment_id));
            if ($mapping_result === false) {
                throw new \Exception('Could not delete appointment-service mapping from the database');
            }

            $wpdb->query('COMMIT');

            return new \WP_REST_Response('Appointment deleted successfully', 200);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new \WP_Error('db_delete_error', $e->getMessage(), ['status' => 500]);
        }
    }

    public function update_appointment_data(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_id = $request['id'];
        $appointment_data = $request->get_json_params();

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $wpdb->query('START TRANSACTION');

        try {
            $result = $wpdb->update($appointments_table, array(
                'name' => sanitize_text_field($appointment_data['name']),
                'surname' => sanitize_text_field($appointment_data['surname']),
                'phone' => sanitize_text_field($appointment_data['phone']),
                'email' => sanitize_email($appointment_data['email']),
                'date' => $appointment_data['date'],
                'startTime' => $appointment_data['startTime'],
                'endTime' => $appointment_data['endTime'],
                'status' => $appointment_data['status']
            ), array('id' => $appointment_id), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'), array('%d'));

            if ($result === false) {
                throw new \Exception('Could not update appointment in the database');
            }

            $mapping_result = $wpdb->delete($mapping_table, array('appointment_id' => $appointment_id));
            if ($mapping_result === false) {
                throw new \Exception('Could not delete appointment-service mapping from the database');
            }

            foreach ($appointment_data['service_id'] as $service_id) {
                $mapping_result = $wpdb->insert($mapping_table, array(
                    'appointment_id' => $appointment_id,
                    'service_id' => $service_id
                ), array('%d', '%d'));

                if ($mapping_result === false) {
                    throw new \Exception('Could not insert appointment-service mapping into the database');
                }
            }

            $wpdb->query('COMMIT');

            return new \WP_REST_Response('Appointment updated successfully', 200);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new \WP_Error('db_update_error', $e->getMessage(), ['status' => 500]);
        }
    }
}
?>