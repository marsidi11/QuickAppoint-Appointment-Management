<p>Your appointment has been successfully cancelled.</p>
<p><strong>Name:</strong> <?php echo esc_html($appointment_data['name'] . ' ' . $appointment_data['surname']); ?><br>
<strong>Phone:</strong> <?php echo esc_html($appointment_data['phone']); ?><br>
<strong>Email:</strong> <?php echo esc_html($appointment_data['email']); ?><br>
<strong>Date:</strong> <?php echo esc_html($appointment_data['date']); ?><br>
<strong>Start Time:</strong> <?php echo esc_html($appointment_data['startTime']); ?><br>
<strong>End Time:</strong> <?php echo esc_html($appointment_data['endTime']); ?></p>