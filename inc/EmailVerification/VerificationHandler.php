<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailVerification;

class VerificationHandler 
{
    public function register() 
    {
        add_action('init', array($this, 'handle_verification_request'));
    }

    public function handle_verification_request() 
    {
        if (isset($_GET['action']) && $_GET['action'] === 'verify_appointment' && isset($_GET['token'])) 
        {
            $this->verify_appointment($_GET);
        }
    }

    public function verify_appointment($request) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $token = sanitize_text_field($request['token']);

        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE token = %s",
            $token
        ));

        if ($appointment && $appointment->status === 'Pending') 
        {
            $wpdb->update($table_name, ['status' => 'Confirmed'], ['token' => $token]);
            echo '<h2>Appointment confirmed successfully!</h2>';
            // include plugin_dir_path(__FILE__) . '../../templates/verification-page.php';
        } else 
        {
            echo '<h2>Invalid or expired token. Please try again.</h2>';
        }
        exit;
    }
}