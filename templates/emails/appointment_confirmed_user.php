<p><?php _e('Your appointment has been confirmed. Thank you for using our service.', 'quickappoint'); ?></p>
<p><?php _e('If you have any questions, please don\'t hesitate to contact us.', 'quickappoint'); ?></p>

<p>
    <strong><?php _e('Name:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getName() . ' ' . $appointment_data->getSurname()); ?><br>
    <strong><?php _e('Phone:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getPhone()); ?><br>
    <strong><?php _e('Email:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getEmail()); ?><br>
    <strong><?php _e('Date:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getDate()); ?><br>
    <strong><?php _e('Start Time:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getStartTime()); ?><br>
    <strong><?php _e('End Time:', 'quickappoint'); ?></strong> <?php echo esc_html($appointment_data->getEndTime()); ?>
</p>

<p>
    <?php _e('Check the appointment information here:', 'quickappoint'); ?> 
    <a href="<?php echo esc_url($check_info_url); ?>"><?php echo esc_url($check_info_url); ?></a>
</p>