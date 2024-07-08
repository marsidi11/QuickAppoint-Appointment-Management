<?php

use Inc\Api\Repositories\AppointmentRepository;
use Inc\EmailConfirmation\EmailSender;

$appointment_repository = new AppointmentRepository();
$email_sender = new EmailSender();

if (isset($_GET['token'])) 
{
    $token = sanitize_text_field($_GET['token']);
    $appointment = $appointment_repository->getAppointmentByToken($token);

    if (!is_wp_error($appointment)) {
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') 
        {
            $result = $appointment_repository->updateAppointmentStatus($token, 'Cancelled');
            if (!is_wp_error($result)) 
            {
                echo '<div class="am-message am-message--success">Your appointment has been cancelled successfully.</div>';
                
                // Send cancellation email to user and admin
                $emailSender->appointment_cancelled_user($appointment->getEmail(), $token);
                $emailSender->appointment_cancelled_admin($token);

                // Refresh appointment data after cancellation
                $appointment = $appointment_repository->getAppointmentByToken($token);
            } else 
            {
                echo '<div class="am-message am-message--error">Failed to cancel the appointment. Please try again later or contact support.</div>';
            }
        }

        // Check if the appointment is in the past
        $appointment_datetime = new DateTime($appointment->getDate() . ' ' . $appointment->getEndTime());
        $is_past = $appointment_datetime < new DateTime();

        // Ensure we're using the correct status from the database
        $status = strtolower($appointment->getStatus());
        $is_cancelled = $status === 'cancelled';
        ?>
        <div class="am-confirmation">
            <h2 class="am-confirmation__title">Appointment Details</h2>
            <div class="am-confirmation__content">
                <div class="am-confirmation__info">
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Full Name:</span>
                        <span class="am-confirmation__value"><?php echo esc_html($appointment->getName() . ' ' . $appointment->getSurname()); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Phone:</span>
                        <span class="am-confirmation__value"><?php echo esc_html($appointment->getPhone()); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Email:</span>
                        <span class="am-confirmation__value"><?php echo esc_html($appointment->getEmail()); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Services:</span>
                        <span class="am-confirmation__value"><?php echo esc_html(implode(', ', $appointment->getServiceNames()) ?: 'No services specified'); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Date:</span>
                        <span class="am-confirmation__value"><?php echo esc_html($appointment->getDate()); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Time:</span>
                        <span class="am-confirmation__value"><?php echo esc_html($appointment->getStartTime() . ' - ' . $appointment->getEndTime()); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Total Price:</span>
                        <span class="am-confirmation__value"><?php echo esc_html(get_option('currency_symbol', '€') . number_format($appointment->getTotalPrice(), 2)); ?></span>
                    </div>
                    <div class="am-confirmation__row">
                        <span class="am-confirmation__label">Status:</span>
                        <span class="am-confirmation__value">
                            <span class="am-status am-status--<?php echo $status; ?>"><?php echo esc_html(ucfirst($status)); ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="am-confirmation__actions">
                <?php if ($is_past): ?>
                    <p class="am-message am-message--info">This appointment has already passed and cannot be modified.</p>
                <?php elseif ($is_cancelled): ?>
                    <p class="am-message am-message--info">This appointment has been cancelled and cannot be modified further.</p>
                <?php else: ?>
                    <form method="post" class="am-confirmation__form">
                        <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                        <button type="submit" name="action" value="cancel" class="am-btn am-btn--danger" onclick="return confirm('Are you sure you want to cancel this appointment? This action cannot be undone.');">
                            Cancel Appointment
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <p>Notes <br>
            1. If your status is "Pending" and you haven't received an email (check spam), your appointment is considered confirmed. If you did receive an email, please click the link within to confirm. A pending status without email is treated as confirmed. <br>
            2. If you cancel your appointment, it cannot be restored - you must create a new one. Also to change the time or services, cancel this appointment and create a new one.
            </p>
        </div>
        <?php
    } else 
    {
        echo '<div class="am-message am-message--error">Appointment details not found. Please check your link and try again.</div>';
    }
} else 
{
    echo '<div class="am-message am-message--error">No appointment token provided. Please use the link from your confirmation email.</div>';
}
?>