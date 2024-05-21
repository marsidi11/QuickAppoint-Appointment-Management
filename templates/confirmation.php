<div id="am_confirmation">
<?php
if (isset($_GET['token']) && isset($_GET['status'])) {
    $token = sanitize_text_field($_GET['token']);
    $status = sanitize_text_field($_GET['status']);
    
    if ($status === 'confirmed') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE token = %s",
            $token
        ));

        if ($appointment) {
            ?>
            <h3>Appointment Details</h3>
            <p>Your appointment has been confirmed successfully. Here are the details:</p>
            <ul>
                <li><strong>Full Name:</strong> <?php echo esc_html($appointment->name . ' ' . $appointment->surname); ?></li>
                <li><strong>Phone:</strong> <?php echo esc_html($appointment->phone); ?></li>
                <li><strong>Email:</strong> <?php echo esc_html($appointment->email); ?></li>
                <li><strong>Date:</strong> <?php echo esc_html($appointment->date); ?></li>
                <li><strong>Start Time:</strong> <?php echo esc_html($appointment->startTime); ?></li>
                <li><strong>End Time:</strong> <?php echo esc_html($appointment->endTime); ?></li>
                <li><strong>Price:</strong> <?php echo esc_html($appointment->price); ?></li>
                <li><strong>Status:</strong> <?php echo esc_html($appointment->status); ?></li>
            </ul>
            <?php
        } else {
            echo '<p>Invalid token. Please try again.</p>';
        }
    } else {
        echo '<p>Verification failed or expired token. Please try again.</p>';
    }
} else {
    echo '<p>No token provided.</p>';
} ?>

</div>
