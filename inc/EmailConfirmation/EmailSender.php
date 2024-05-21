<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailConfirmation;

class EmailSender
{
    public function send_confirmation_email($email, $token) 
    {
        $confirmation_url = add_query_arg([
            'token' => $token,
            'action' => 'verify_appointment'
        ], home_url());

        $subject = 'Please verify your appointment';
        $message = "Please click the following link to verify your appointment: <a href='$confirmation_url'>$confirmation_url</a>";

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($email, $subject, $message, $headers);
    }
}