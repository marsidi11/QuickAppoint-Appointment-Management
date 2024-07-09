<p>Your appointment has been confirmed. Thank you for using our service.</p>
<p>If you have any questions, please don't hesitate to contact us.</p>

<p><strong>Name:</strong> <?php echo esc_html($appointment_data->getName() . ' ' . $appointment_data->getSurname()); ?><br>
<strong>Phone:</strong> <?php echo esc_html($appointment_data->getPhone()); ?><br>
<strong>Email:</strong> <?php echo esc_html($appointment_data->getEmail()); ?><br>
<strong>Date:</strong> <?php echo esc_html($appointment_data->getDate()); ?><br>
<strong>Start Time:</strong> <?php echo esc_html($appointment_data->getStartTime()); ?><br>
<strong>End Time:</strong> <?php echo esc_html($appointment_data->getEndTime()); ?></p>

<p>Check the appointment information here: <a href="<?php echo $check_info_url; ?>"><?php echo $check_info_url; ?></a></p>