<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api;

/**
 * Custom REST API controller for handling custom data. 
 * Endpoints for getting all services and creating new services.
 */

 // TODO: Add validation for all the service data
class ServicesDataController extends RestController 
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

        // Route to delete a service
        \register_rest_route( $this->get_namespace(), $this->get_base() . '/delete/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_service_data'),
            'permission_callback' => function () 
            {
                return current_user_can('edit_posts');
            }
        ));

    }

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }
        return true;
    }

    public function get_all_services(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $query = "SELECT id, name, description, FLOOR(TIME_TO_SEC(duration)/60) as duration, price FROM $table_name";

        $appointments = $wpdb->get_results($query);

        return new \WP_REST_Response($appointments, 200);
    }

    public function post_service_data(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_data = $request->get_json_params();

        // Validate the appointment data
        if (!isset($appointment_data['name']) || !isset($appointment_data['price']) || !isset($appointment_data['duration'])) 
        {
            return new WP_Error('invalid_request', 'Invalid appointment data', array('status' => 400));
        }

        // Insert the services data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        // Convert the [duration] to the right format
        $minutes = $appointment_data['duration'];
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);
        $time = sprintf("%02d:%02d:00", $hours, $minutes);

        $result = $wpdb->insert($table_name, array
        (
            'name' => sanitize_text_field($appointment_data['name']),

            'description' => sanitize_text_field($appointment_data['description']),

            'duration' => $time,

            'price' => $appointment_data['price'],
        ));

        if ($result === false) 
        {
            return new \WP_Error('db_insert_error', 'Could not insert services into the database', array('status' => 500));
        }

        return new \WP_REST_Response('Service created successfully', 201);
        
    }
    // TODO: When a service is deleted, all appointments that have that service should are also be deleted. Find a way to fix this.
    public function delete_service_data(\WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_id = $request['id'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $result = $wpdb->delete($table_name, array('id' => $service_id));

        if ($result === false) 
        {
            return new \WP_Error('db_delete_error', 'Could not delete service from the database', array('status' => 500));
        }

        return new \WP_REST_Response('Service deleted successfully', 200);
    }

}