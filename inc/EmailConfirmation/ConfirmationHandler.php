<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\EmailConfirmation;

use Inc\Base\BaseController;
use Inc\Api\Repositories\AppointmentRepository;
use WP_Error;

class ConfirmationHandler extends BaseController
{
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        parent::__construct();
        $this->appointmentRepository = $appointmentRepository;
    }

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
        $token = sanitize_text_field($request['token']);
        
        $result = $this->appointmentRepository->getAppointmentByToken($token);

        if (is_wp_error($result)) {
            wp_redirect(home_url('/appointment-confirmation?status=error&message=' . urlencode($result->get_error_message())));
            exit;
        }

        if ($result && $result->getStatus() === 'Pending')
        {
            $update_result = $this->appointmentRepository->updateAppointmentStatus($token, 'Confirmed');
            
            if (is_wp_error($update_result)) 
            {
                wp_redirect(home_url('/appointment-confirmation?status=error&message=' . urlencode($update_result->get_error_message())));
                exit;
            }

            // Send confirmation email to user and admin
            $emailSender = new EmailSender();
            $emailSender->appointment_confirmed_user($result->getEmail(), $result, $token);
            $emailSender->appointment_confirmed_admin($result, $token);

            wp_redirect(home_url('/appointment-confirmation?token=' . $token . '&status=confirmed'));
        } else
        {
            wp_redirect(home_url('/appointment-confirmation?token=' . $token . '&status=check_appointment_info'));
        }
        exit;
    }
}