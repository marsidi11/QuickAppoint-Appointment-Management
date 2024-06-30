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

    public function getAllAppointments($page)
    {
        return $this->appointmentRepository->getAllAppointments($page);
    }

    public function createAppointment($appointmentData)
    {
        // Validate the appointment data
        $validation = $this->validateAppointmentData($appointmentData);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Generate the token for email confirmation
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $appointmentData['token'] = $token;
        $appointmentData['status'] = 'Pending';

        $appointment = new Appointment($appointmentData);
        $result = $this->appointmentRepository->createAppointment($appointment);

        if (is_wp_error($result)) {
            return $result;
        }

        // Send confirmation email to user
        $this->emailSender->send_confirmation_email_to_user($appointment->getEmail(), $token);

        // Notify admin about the new appointment
        $admin_email = get_option('admin_email');
        $this->emailSender->notify_admin_about_appointment($admin_email, $appointmentData, $token);

        return true;
    }

    public function deleteAppointment($appointmentId)
    {
        return $this->appointmentRepository->deleteAppointment($appointmentId);
    }

    public function updateAppointment($appointmentId, $appointmentData)
    {
        // Validate the appointment data
        $validation = $this->validateAppointmentData($appointmentData);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $appointment = new Appointment($appointmentData);
        $appointment->setId($appointmentId);

        return $this->appointmentRepository->updateAppointment($appointment);
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