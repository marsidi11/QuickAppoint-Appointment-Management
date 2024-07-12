<?php

/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\Base;

class Deactivate
{
    public static function deactivate()
    {
        global $wpdb;

        // Define table names
        $table_names = [
            'appointments' => $wpdb->prefix . 'quickappoint_appointments',
            'services'     => $wpdb->prefix . 'quickappoint_services',
            'mapping'      => $wpdb->prefix . 'quickappoint_mapping',
        ];

        // Remove foreign key constraints
        $queries = [
            "ALTER TABLE {$table_names['mapping']} DROP FOREIGN KEY fk_appointment_id;",
            "ALTER TABLE {$table_names['mapping']} DROP FOREIGN KEY fk_service_id;"
        ];

        foreach ($queries as $query) {
            $wpdb->query($query);
        }

        // Drop tables
        foreach ($table_names as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        // Delete options
        delete_option('quickappoint_plugin_version');
        delete_option('notifications_email');

        // Optionally, delete pages created by the plugin
        $pages = [
            'Make an Appointment',
            'Appointment Confirmation'
        ];

        foreach ($pages as $page_title) {
            $page_query = new \WP_Query([
                'post_type' => 'page',
                'title'     => $page_title,
                'post_status' => 'any',
                'posts_per_page' => 1
            ]);

            if ($page_query->have_posts()) {
                while ($page_query->have_posts()) {
                    $page_query->the_post();
                    wp_delete_post(get_the_ID(), true);
                }
                wp_reset_postdata();
            }
        }
    }
}
