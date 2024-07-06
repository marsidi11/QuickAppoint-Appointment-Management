<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\EmailConfirmation;

use Inc\Base\BaseController;
use WP_Error;

class EmailSender extends BaseController {
    /**
     * Send confirmation email to the user
     *
     * @param string $user_email The user's email address
     * @param string $token The verification token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function send_confirmation_email_to_user($user_email, $token) {
        if (!is_email($user_email)) {
            return new WP_Error('invalid_email', 'Invalid email address');
        }

        $confirmation_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'verify_appointment'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('amp_confirmation_email_subject', 'Please verify your appointment');
        $message = $this->get_email_template('user_confirmation', [
            'confirmation_url' => $confirmation_url
        ]);

        return $this->send_email($user_email, $subject, $message);
    }

    /**
     * Notify admin about a new appointment
     *
     * @param string $admin_email The admin's email address
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function notify_admin_about_appointment($admin_email, $appointment_data, $token) 
    {
        if (!is_email($admin_email)) {
            return new WP_Error('invalid_email', 'Invalid admin email address');
        }

        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('amp_admin_notification_subject', 'New Appointment');
        $message = $this->get_email_template('admin_notification', [
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        return $this->send_email($admin_email, $subject, $message);
    }

    /**
     * Send an email to when the appointment is confirmed to the user
     *
     * @param string $user_email The user's email address
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function send_appointment_confirmed_email($user_email, $token)
    {
        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('amp_appointment_confirmed_subject', 'Your appointment has been confirmed');
        $message = $this->get_email_template('appointment_confirmed', [
            'user_email' => $user_email,
            'check_info_url' => $check_info_url
        ]);

        return $this->send_email($user_email, $subject, $message);
    }

    /**
     * Get email template
     *
     * @param string $template_name The name of the template
     * @param array $data Data to be used in the template
     * @return string The email content
     */
    private function get_email_template($template_name, $data) 
    {
        $template_path = apply_filters('amp_email_template_path', $this->plugin_path . "templates/emails/{$template_name}.php");
        
        if (!file_exists($template_path)) {
            return '';
        }

        ob_start();
        extract($data);
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Send an email
     *
     * @param string $to Email recipient
     * @param string $subject Email subject
     * @param string $message Email content
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private function send_email($to, $subject, $message) 
    {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];

        $sent = wp_mail($to, $subject, $message, $headers);

        if (!$sent) 
        {
            return new WP_Error('email_not_sent', 'Failed to send email');
        }

        return true;
    }
}