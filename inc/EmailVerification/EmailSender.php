<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailVerification;

class EmailSender
{
    public function send_verification_email($email, $token) 
    {
        $verification_url = add_query_arg([
            'token' => $token,
            'action' => 'verify_appointment'
        ], home_url());

        $subject = 'Please verify your appointment';
        $message = "Please click the following link to verify your appointment: <a href='$verification_url'>$verification_url</a>";

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($email, $subject, $message, $headers);
    }
}