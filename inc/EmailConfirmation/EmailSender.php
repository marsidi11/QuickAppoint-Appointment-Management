<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\EmailConfirmation;

class EmailSender
{
    public function send_confirmation_email_to_user($user_email, $token) 
    {
        $confirmation_url = add_query_arg([
            'token' => $token,
            'action' => 'verify_appointment'
        ], home_url('/appointment-confirmation'));

        $subject = 'Please verify your appointment';
        $message = "Please click the following link to verify your appointment: <a href='$confirmation_url'>$confirmation_url</a>";

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Send email to the user
        wp_mail($user_email, $subject, $message, $headers);
    }

    public function notify_admin_about_appointment($admin_email, $appointment_data, $token) 
    {
        $confirmation_url = add_query_arg([
            'token' => $token,
            'action' => 'verify_appointment'
        ], home_url('/appointment-confirmation'));

        $subject = 'New Appointment Request';
        $message = "
            A new appointment has been scheduled.<br>
            <strong>Name:</strong> {$appointment_data['name']} {$appointment_data['surname']}<br>
            <strong>Phone:</strong> {$appointment_data['phone']}<br>
            <strong>Email:</strong> {$appointment_data['email']}<br>
            <strong>Date:</strong> {$appointment_data['date']}<br>
            <strong>Start Time:</strong> {$appointment_data['startTime']}<br>
            <strong>End Time:</strong> {$appointment_data['endTime']}<br>
            <br>
            Please verify the appointment here: <a href='$confirmation_url'>$confirmation_url</a>
        ";

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Send email to the admin
        wp_mail($admin_email, $subject, $message, $headers);
    }
}