<?php

/**
 * @package AppointmentManagementPlugin
 * 
 * This class handles the activation process of the Appointment Management Plugin.
 * It includes methods to:
 * - Create necessary database tables for storing appointment and service data.
 * - Set up foreign key relationships between the tables.
 * - Create 2 pages "Make an Appointment" & "Confirmation".
 */

namespace Inc\Base;

class Activate
{
   public static function activate()
   {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $appointments_table_name = $wpdb->prefix . 'am_appointments';
      $services_table_name = $wpdb->prefix . 'am_services';
      $mapping_table_name = $wpdb->prefix . 'am_mapping';

      // Create the tables
      self::create_tables($wpdb, $charset_collate, $appointments_table_name, $services_table_name, $mapping_table_name);
      // Add foreign keys
      self::add_foreign_keys($wpdb, $mapping_table_name, $appointments_table_name, $services_table_name);
      // Create pages for appointment management
      self::create_pages();
   }

   private static function create_tables($wpdb, $charset_collate, $appointments_table_name, $services_table_name, $mapping_table_name)
   {
      $tables = [
         "CREATE TABLE $appointments_table_name (
              id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              name varchar(255) NOT NULL,
              surname varchar(255) NOT NULL,
              phone varchar(20) NOT NULL,
              email varchar(255) NOT NULL,
              date date NOT NULL,
              startTime time NOT NULL,
              endTime time NOT NULL,
              price decimal(10, 2) NOT NULL,
              status varchar(20) NOT NULL DEFAULT 'Pending',
              token varchar(255) NOT NULL
          ) $charset_collate;",

         "CREATE TABLE $services_table_name (
              id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              name varchar(255) NOT NULL,
              description TEXT NOT NULL,
              duration time NOT NULL,
              price decimal(10, 2) NOT NULL,
              UNIQUE KEY name (name)
          ) $charset_collate;",

         "CREATE TABLE $mapping_table_name (
              id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              appointment_id mediumint(9) NOT NULL,
              service_id mediumint(9) NOT NULL,
              UNIQUE KEY unique_appointment_service (appointment_id, service_id)
          ) $charset_collate;"
      ];

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      foreach ($tables as $sql) {
         dbDelta($sql);
      }
   }

   private static function add_foreign_keys($wpdb, $mapping_table_name, $appointments_table_name, $services_table_name)
   {
      $queries = [
         "ALTER TABLE $mapping_table_name
           ADD CONSTRAINT fk_appointment_id
           FOREIGN KEY (appointment_id)
           REFERENCES $appointments_table_name(id)
           ON DELETE CASCADE;",

         "ALTER TABLE $mapping_table_name
           ADD CONSTRAINT fk_service_id
           FOREIGN KEY (service_id)
           REFERENCES $services_table_name(id)
           ON DELETE CASCADE;"
      ];

      foreach ($queries as $query) {
         $result = $wpdb->query($query);
         if ($result === false) {
            error_log('Error executing query: ' . $wpdb->last_error);
         }
      }
   }

   private static function create_pages()
   {
      // Page titles and shortcodes
      $pages = [
         ['title' => 'Make an Appointment', 'shortcode' => '[am_form]'],
         ['title' => 'Appointment Confirmation', 'shortcode' => '[am_confirmation]']
      ];

      foreach ($pages as $page_info) {
         // Check if the page already exists
         $page = get_page_by_title($page_info['title']);
         if (!$page) {
            // Create post object
            $page_data = array(
               'post_title'    => $page_info['title'],
               'post_content'  => $page_info['shortcode'],
               'post_status'   => 'publish',
               'post_type'     => 'page'
            );

            // Insert the post into the database
            wp_insert_post($page_data);
         }
      }
   }
}
