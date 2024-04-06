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
      $table_name = $wpdb->prefix . 'am_bookings';

      $sql = "CREATE TABLE $table_name 
      (
         id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
         name varchar(255) NOT NULL,
         surname varchar(255) NOT NULL,
         phone varchar(20) NOT NULL,
         email varchar(255) NOT NULL,
         services TEXT NOT NULL,
         date date NOT NULL,
         startTime time NOT NULL,
         endTime time NOT NULL
      ) $charset_collate;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }
 }