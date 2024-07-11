<?php

/**
 * Appointment Service
 *
 * This file defines the AppointmentService class, responsible for handling the business logic
 * related to appointments. It includes methods for creating, updating, deleting, and retrieving
 * appointments. This class ensures that all business rules and validations are applied before
 * interacting with the repository.
 */

namespace Inc\Api\Services;

use Inc\Api\Repositories\AppointmentRepository;
use Inc\EmailConfirmation\EmailSender;
use Inc\Api\Models\Appointment;
use WP_Error;

class AppointmentService
{
    private $appointmentRepository;
    private $emailSender;

    public function __construct(AppointmentRepository $appointmentRepository, EmailSender $emailSender)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->emailSender = $emailSender;
    }

    /**
     * Retrieve all appointments.
     *
     * Fetches appointments from the database based on the specified page and number of items per  * page.
     * This can be used to implement pagination in the appointment listing.
     *
     * @param int $page The current page number.
     * @param int $per_page The number of appointments to display per page.
     * @return array An array of appointments.
     */
    public function getAllAppointments($page, $per_page)
    {
        return $this->appointmentRepository->getAllAppointments($page, $per_page);
    }

    /**
     * Create a new appointment.
     *
     * Validates the appointment data, generates a confirmation token, creates the appointment,
     * sends confirmation emails to the user and admin, and returns the confirmation URL.
     *
     * @param array $appointmentData The appointment data.
     * @return array|WP_Error An array with success status, message, and confirmation URL on success, WP_Error on failure.
     */
    public function createAppointment($appointmentData)
    {
        // Validate the appointment data
        $validation = $this->validateAppointmentData($appointmentData);
        if (is_wp_error($validation)) 
        {
            return $validation;
        }

        // Generate the token for email confirmation
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $appointmentData['token'] = $token;
        $appointmentData['status'] = 'Pending';

        $appointment = new Appointment($appointmentData);
        $result = $this->appointmentRepository->createAppointment($appointment);

        if (is_wp_error($result)) 
        {
            return $result;
        }

        // Send confirmation email to user
        $user_email_result = $this->emailSender->new_appointment_user($appointment->getEmail(), $token);
        if (is_wp_error($user_email_result)) 
        {
            error_log('Failed to send user confirmation email: ' . $user_email_result->get_error_message());
        }   

        // Notify admin about the new appointment
        $admin_email_result = $this->emailSender->new_appointment_admin($appointmentData, $token);
        if (is_wp_error($admin_email_result)) 
        {
            error_log('Failed to send admin notification email: ' . $admin_email_result->get_error_message());
        }

        // Generate the confirmation URL with the full website address
        $site_url = get_site_url();
        $confirmation_url = $site_url . "/appointment-confirmation?token={$token}&action=check_appointment_info";

        return [
            'success' => true,
            'message' => 'Appointment created successfully.',
            'confirmation_url' => $confirmation_url
        ];
    }

    /**
     * Delete an appointment.
     *
     * Removes an appointment from the database based on the provided appointment ID.
     *
     * @param int $appointmentId The ID of the appointment to delete.
     * @return bool True on successful deletion, false on failure.
     */
    public function deleteAppointment($appointmentId)
    {
        return $this->appointmentRepository->deleteAppointment($appointmentId);
    }

    /**
     * Update an appointment.
     *
     * Validates the provided appointment data and updates the appointment in the database.
     * Returns true on success or WP_Error on failure.
     *
     * @param int $appointmentId The ID of the appointment to update.
     * @param array $appointmentData The new data for the appointment.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    // public function updateAppointment(int $appointmentId, Appointment $appointmentData)
    // {
    //     $validation = $this->validateAppointmentData($appointmentData);
    //     if (is_wp_error($validation)) {
    //         return $validation;
    //     }

    //     $appointment = new Appointment($appointmentData);
    //     $updatedAppointment = $this->appointmentRepository->updateAppointment($appointmentId, $appointment);

    //     if (!$updatedAppointment) {
    //         error_log('Error: Failed to update appointment at ' . date('Y-m-d H:i:s'));
    //     }

    //     return $updatedAppointment;
    // }

    /**
     * Update the status of an appointment and notify relevant parties.
     *
     * This method updates the status of an appointment in the database based on the provided appointment ID.
     * Upon successful update, it sends notification emails to the user and admin, with the content varying depending on the new status of the appointment (Cancelled, Confirmed, Pending).
     *
     * @param int $appointmentId The ID of the appointment to update.
     * @param string $status The new status for the appointment.
     * @return bool True on successful update and email notifications, false on failure.
     */
    public function updateAppointmentStatusById(int $appointmentId, string $status)
    {
        // Fetch the current appointment data
        $appointmentData = $this->appointmentRepository->getAppointmentById($appointmentId);
        $currentStatus = $appointmentData->getStatus();

        // If the new status is the same as the current status, do nothing
        if ($currentStatus === $status) {
            return $appointmentData;
        }

        // Update the appointment status
        $updatedAppointment = $this->appointmentRepository->updateAppointmentStatusById($appointmentId, $status);

        if (!$updatedAppointment) {
            error_log('Error: Failed to update appointment at ' . date('Y-m-d H:i:s'));
            return false;
        }

        $email = $appointmentData->getEmail();
        $token = $appointmentData->getToken();

        // Send appropriate emails based on the new status
        switch ($status) {
            case 'Cancelled':
                $this->emailSender->appointment_cancelled_user($email, $appointmentData, $token);
                $this->emailSender->appointment_cancelled_admin($appointmentData, $token);
                break;

            case 'Confirmed':
                $this->emailSender->appointment_confirmed_user($email, $appointmentData, $token);
                $this->emailSender->appointment_confirmed_admin($appointmentData, $token);
                break;

            case 'Pending':
                $this->emailSender->new_appointment_user($email, $appointmentData, $token);
                $this->emailSender->new_appointment_admin($appointmentData, $token);
                break;
        }

        return $updatedAppointment;
    }



    private function validateAppointmentData($data)
    {
        $required_fields = ['name', 'surname', 'phone', 'email', 'date', 'startTime', 'endTime', 'service_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('invalid_request', "Field '$field' is required and cannot be empty", ['status' => 400]);
            }
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $data['name'])) {
            return new WP_Error('invalid_name', 'Name can only contain letters and whitespace', ['status' => 400]);
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $data['surname'])) {
            return new WP_Error('invalid_surname', 'Surname can only contain letters and whitespace', ['status' => 400]);
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['date'])) {
            return new WP_Error('invalid_date', 'Date must be in the format YYYY-MM-DD', ['status' => 400]);
        }

        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $data['phone'])) {
            return new WP_Error('invalid_phone', 'Invalid phone number format', ['status' => 400]);
        }

        $start_time = strtotime($data['startTime']);
        $end_time = strtotime($data['endTime']);
        if ($start_time === false || $end_time === false || $end_time <= $start_time) {
            return new WP_Error('invalid_time', 'Invalid start or end time', ['status' => 400]);
        }

        if (!is_array($data['service_id']) || empty($data['service_id'])) {
            return new WP_Error('invalid_service_id', 'Service ID must be a non-empty array', ['status' => 400]);
        }

        $email = sanitize_email($data['email']);
        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'Invalid email address', ['status' => 400]);
        }

        return true;
    }
}