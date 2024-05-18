<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\Base;

class Activate {
    public static function activate() 
    {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $appointments_table_name = $wpdb->prefix . 'am_appointments';
      $services_table_name = $wpdb->prefix . 'am_services';
      $mapping_table_name = $wpdb->prefix . 'am_mapping';

      // TODO: Add the booked date, time, price to the table
      $sql1 = "CREATE TABLE $appointments_table_name 
      (
         id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
         name varchar(255) NOT NULL,
         surname varchar(255) NOT NULL,
         phone varchar(20) NOT NULL,
         email varchar(255) NOT NULL,
         date date NOT NULL,
         startTime time NOT NULL,
         endTime time NOT NULL,
         status varchar(20) NOT NULL
      ) $charset_collate;";


      $sql2 = "CREATE TABLE $services_table_name 
      (
         id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
         name varchar(255) NOT NULL,
         description TEXT NOT NULL,
         duration time NOT NULL,
         price decimal(10, 2) NOT NULL,
         UNIQUE KEY name (name)
      ) $charset_collate;";


      $sql3 = "CREATE TABLE $mapping_table_name
      (
         id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
         appointment_id mediumint(9) NOT NULL,
         service_id mediumint(9) NOT NULL,
         UNIQUE KEY unique_appointment_service (appointment_id, service_id)
      ) $charset_collate;";


      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql1);
      dbDelta($sql2);
      dbDelta($sql3);

      // Add foregin keys to the mapping table
      $wpdb->query("
         ALTER TABLE $mapping_table_name
         ADD CONSTRAINT fk_appointment_id
            FOREIGN KEY (appointment_id)
            REFERENCES $appointments_table_name(id)
            ON DELETE CASCADE;
      ");

      $wpdb->query("
         ALTER TABLE $mapping_table_name
         ADD CONSTRAINT fk_service_id
            FOREIGN KEY (service_id)
            REFERENCES $services_table_name(id)
            ON DELETE CASCADE;
      ");
   }
}