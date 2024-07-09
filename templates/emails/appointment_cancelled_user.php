<p>Your appointment has been successfully cancelled.</p>
<p><strong>Name:</strong> <?php echo esc_html($appointment_data->getName() . ' ' . $appointment_data->getSurname()); ?><br>
<strong>Phone:</strong> <?php echo esc_html($appointment_data->getPhone()); ?><br>
<strong>Email:</strong> <?php echo esc_html($appointment_data->getEmail()); ?><br>
<strong>Date:</strong> <?php echo esc_html($appointment_data->getDate()); ?><br>
<strong>Start Time:</strong> <?php echo esc_html($appointment_data->getStartTime()); ?><br>
<strong>End Time:</strong> <?php echo esc_html($appointment_data->getEndTime()); ?></p>
<strong>Status:</strong> <?php echo esc_html(ucfirst($appointment_data->getStatus())); ?></p>
<p>Check the appointment information here: <a href="<?php echo $check_info_url; ?>"><?php echo $check_info_url; ?></a></p>
