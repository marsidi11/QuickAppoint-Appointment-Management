<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\EmailConfirmation;

use Inc\Base\BaseController;
use WP_Error;

class EmailSender extends BaseController 
{   
    private $admin_emails = [];

    public function __construct() 
    {
        parent::__construct(); 
        $this->admin_emails = $this->get_admin_emails();
    }

    /**
     * Send confirmation email to the user
     *
     * @param string $user_email The user's email address
     * @param string $token The verification token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function new_appointment_user($user_email, $token) {
        if (!is_email($user_email)) {
            return new WP_Error('invalid_email', 'Invalid email address');
        }

        $confirmation_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'verify_appointment'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_new_appointment_user_subject', 'Please verify your appointment');
        $message = $this->get_email_template('new_appointment_user', [
            'confirmation_url' => $confirmation_url
        ]);

        return $this->send_email($user_email, $subject, $message);
    }

    /**
     * Notify admin about a new appointment
     *
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function new_appointment_admin($appointment_data, $token) 
    {
        if (empty($this->admin_emails)) 
        {
            return new WP_Error('no_admin_emails', 'No valid admin email addresses found');
        }

        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_new_appointment_admin_subject', 'New Appointment');
        $message = $this->get_email_template('new_appointment_admin', [
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        foreach ($this->admin_emails as $admin_email) 
        {
            if (is_email($admin_email)) {
                $this->send_email($admin_email, $subject, $message);
            }
        }
    
        return true;
    }

    /**
     * Send an email when the appointment is confirmed to the user
     *
     * @param string $user_email The user's email address
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function appointment_confirmed_user($user_email, $appointment_data, $token)
    {
        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_appointment_confirmed_user_subject', 'Your appointment has been confirmed');
        $message = $this->get_email_template('appointment_confirmed_user', [
            'user_email' => $user_email,
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        return $this->send_email($user_email, $subject, $message);
    }

    /**
     * Send an email when the appointment is confirmed to the admin
     *
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function appointment_confirmed_admin($appointment_data, $token)
    {
        if (empty($this->admin_emails)) {
            return new WP_Error('no_admin_emails', 'No valid admin email addresses found');
        }

        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_appointment_confirmed_admin_subject', 'An appointment has been confirmed');
        $message = $this->get_email_template('appointment_confirmed_admin', [
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        foreach ($this->admin_emails as $admin_email) 
        {
            if (is_email($admin_email)) 
            {
                $this->send_email($admin_email, $subject, $message);
            }
        }

        return true; 
    }

    /**
     * Send an email when the appointment is cancelled to the user
     *
     * @param string $user_email The user's email address
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function appointment_cancelled_user($user_email, $appointment_data, $token)
    {
        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_appointment_cancelled_user_subject', 'Your appointment has been successfully cancelled.');
        $message = $this->get_email_template('appointment_cancelled_user', [
            'user_email' => $user_email,
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        return $this->send_email($user_email, $subject, $message);
    }

    /**
     * Send an email when the appointment is cancelled to the admin
     *
     * @param array $appointment_data The appointment details
     * @param string $token The appointment token
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function appointment_cancelled_admin($appointment_data, $token)
    {
        if (empty($this->admin_emails)) {
            return new WP_Error('no_admin_emails', 'No valid admin email addresses found');
        }

        $check_info_url = esc_url(add_query_arg([
            'token' => urlencode($token),
            'action' => 'check_appointment_info'
        ], home_url('/appointment-confirmation')));

        $subject = apply_filters('quickappoint_appointment_cancelled_admin_subject', 'An appointment has been cancelled.');
        $message = $this->get_email_template('appointment_cancelled_admin', [
            'appointment_data' => $appointment_data,
            'check_info_url' => $check_info_url
        ]);

        foreach ($this->admin_emails as $admin_email) 
        {
            if (is_email($admin_email)) 
            {
                $this->send_email($admin_email, $subject, $message);
            }
        }

        return true;
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
        $template_path = apply_filters('quickappoint_email_template_path', $this->plugin_path . "templates/emails/{$template_name}.php");
        
        if (!file_exists($template_path)) 
        {
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

    /**
     * Get admin emails
     * 
     * @return array Admin emails
     */
    public function get_admin_emails() 
    {
        $emails = get_option('notifications_email');
        if (!$emails) 
        {
            return [];
        }
        $email_array = explode(',', $emails);
        $email_array = array_map('trim', $email_array);
        return array_filter($email_array, 'is_email');
    }
}
