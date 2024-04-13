<?php
/**
 * @package BookingManagementPlugin
 */

 namespace Inc\Base;

 class Activate {
    public static function activate() 
    {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $bookings_table_name = $wpdb->prefix . 'am_bookings';
      $services_table_name = $wpdb->prefix . 'am_services';

      // TODO: Add the booked date and time to the table
      $sql1 = "CREATE TABLE $bookings_table_name 
      (
         id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
         name varchar(255) NOT NULL,
         surname varchar(255) NOT NULL,
         phone varchar(20) NOT NULL,
         email varchar(255) NOT NULL,
         service_id mediumint(9) NOT NULL,
         date date NOT NULL,
         startTime time NOT NULL,
         endTime time NOT NULL,
         FOREIGN KEY (service_id) REFERENCES $services_table_name(id)
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


      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      $wpdb->show_errors();
      dbDelta($sql1);
      dbDelta($sql2);
      $wpdb->hide_errors();
    }
 }