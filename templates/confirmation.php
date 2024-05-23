<div id="am_confirmation">
<?php
if (isset($_GET['token']) && isset($_GET['status'])) {
    $token = sanitize_text_field($_GET['token']);
    $status = sanitize_text_field($_GET['status']);
    
    if ($status === 'confirmed') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $services_table = $wpdb->prefix . 'am_services';
        $mapping_table = $wpdb->prefix . 'am_mapping';
        
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, 
                GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
                SUM(s.price) as total_price
            FROM $table_name a
            LEFT JOIN $mapping_table m ON a.id = m.appointment_id
            LEFT JOIN $services_table s ON m.service_id = s.id
            WHERE a.token = %s
            GROUP BY a.id",
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
                <li><strong>Services:</strong> <?php echo esc_html($appointment->service_names); ?></li>
                <li><strong>Date:</strong> <?php echo esc_html($appointment->date); ?></li>
                <li><strong>Start Time:</strong> <?php echo esc_html($appointment->startTime); ?></li>
                <li><strong>End Time:</strong> <?php echo esc_html($appointment->endTime); ?></li>
                <li><strong>Total Price:</strong> <?php echo esc_html(get_option('currency_symbol') . $appointment->total_price); ?></li>                <li><strong>Status:</strong> <?php echo esc_html($appointment->status); ?></li>
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
