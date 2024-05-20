<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailVerification;

class VerificationHandler 
{
    public function verify_appointment($request) 
    {
        global $wpdb;
        $table = $wpdb->prefix . 'your_appointments_table';
        $token = sanitize_text_field($request['token']);

        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE token = %s",
            $token
        ));

        if ($appointment && $appointment->status === 'pending') 
        {
            $wpdb->update($table, ['status' => 'confirmed'], ['token' => $token]);
            include plugin_dir_path(__FILE__) . '../../templates/verification-page.php';
        } else {
            echo '<h2>Invalid or expired token. Please try again.</h2>';
        }
        exit;
    }
}