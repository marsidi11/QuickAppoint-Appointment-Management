<p><?php _e('A new appointment has been scheduled.', 'quickappoint'); ?></p>
<p>
    <strong><?php _e('Name:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['name'] . ' ' . $appointment_data['surname']); ?><br>
    <strong><?php _e('Phone:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['phone']); ?><br>
    <strong><?php _e('Email:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['email']); ?><br>
    <strong><?php _e('Date:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['date']); ?><br>
    <strong><?php _e('Start Time:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['startTime']); ?><br>
    <strong><?php _e('End Time:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data['endTime']); ?>
</p>
<p>
    <?php _e('Check the appointment information here:', 'quickappoint'); ?> 
    <a href="<?php echo esc_url($check_info_url); ?>"><?php echo esc_url($check_info_url); ?></a>
</p>