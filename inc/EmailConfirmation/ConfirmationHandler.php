<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailConfirmation;

class ConfirmationHandler 
{
    public function register() 
    {
        add_action('init', array($this, 'handle_confirmation_request'));
    }

    public function handle_confirmation_request() 
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
            \wp_redirect(home_url('/appointment-confirmation?token=' . $token . '&status=confirmed'));
        } else 
        {
            \wp_redirect(home_url('/appointment-confirmation?token=' . $token . '&status=confirmed'));
        }
        exit;
    }
}