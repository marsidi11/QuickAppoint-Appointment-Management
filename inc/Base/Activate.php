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
         id mediumint(9) NOT NULL AUTO_INCREMENT,
         name tinytext NOT NULL,
         date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
         PRIMARY KEY  (id)
      ) $charset_collate;";

      require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');
      \dbDelta($sql);
    }
 }