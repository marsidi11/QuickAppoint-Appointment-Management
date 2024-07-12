<?php

/**
 * Appointment Management Plugin Activation
 *
 * @package AppointmentManagementPlugin
 * @subpackage Base
 * @since 1.0.0
 */

namespace Inc\Base;

use WP_Error;

defined('ABSPATH') || exit; // Prevent direct access

/**
 * Class Activate
 *
 * Handles the activation process of the Appointment Management Plugin.
 *
 * @since 1.0.0
 */
class Activate
{

    /**
     * Minimum required WordPress version.
     *
     * @var string
     */
    const REQUIRED_WP_VERSION = '5.0';

    /**
     * Minimum required PHP version.
     *
     * @var string
     */
    const REQUIRED_PHP_VERSION = '7.0';

    /**
     * Path to the plugin's log file.
     *
     * @var string
     */
    const LOG_FILE = WP_CONTENT_DIR . '/appointment-management-plugin.log';

    /**
     * Activation hook callback.
     *
     * Initializes the plugin's database tables and necessary pages.
     *
     * @since 1.0.0
     * @return void
     */
    public static function activate()
    {
        global $wpdb;

        ob_start();

        try {
            self::check_compatibility();

            $charset_collate = $wpdb->get_charset_collate();
            $table_names = [
                'appointments' => $wpdb->prefix . 'quickappoint_appointments',
                'services'     => $wpdb->prefix . 'quickappoint_services',
                'mapping'      => $wpdb->prefix . 'quickappoint_mapping',
            ];

            self::create_or_update_tables($wpdb, $charset_collate, $table_names);
            self::add_foreign_keys($wpdb, $table_names);
            self::create_pages();

            update_option('quickappoint_plugin_version', '1.0.0');

            // Set 'notifications_email' option to the admin email
            $admin_email = get_option('admin_email');
            update_option('notifications_email', $admin_email);

            ob_end_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            self::log_error('Activation failed: ' . $e->getMessage());
            wp_die('Activation failed. Please check the error log for more details.');
        }
    }

    /**
     * Check WordPress and PHP version compatibility.
     *
     * @since 1.0.0
     * @throws \Exception If compatibility check fails.
     * @return void
     */
    private static function check_compatibility()
    {
        global $wp_version;

        if (version_compare($wp_version, self::REQUIRED_WP_VERSION, '<')) {
            throw new \Exception("WordPress version " . self::REQUIRED_WP_VERSION . " or higher is required.");
        }

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            throw new \Exception("PHP version " . self::REQUIRED_PHP_VERSION . " or higher is required.");
        }
    }

    /**
     * Create necessary database tables.
     *
     * @since 1.0.0
     * @param \wpdb   $wpdb            WordPress database access abstraction object.
     * @param string  $charset_collate Database charset and collation.
     * @param array   $table_names     Array of table names.
     * @throws \Exception If table creation or update fails.
     * @return void
     */
    private static function create_or_update_tables($wpdb, $charset_collate, $table_names)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Define the expected table structure
        $tables = [
            'appointments' => "
            CREATE TABLE {$table_names['appointments']} (
                id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                surname varchar(255) NOT NULL,
                phone varchar(20) NOT NULL,
                email varchar(255) NOT NULL,
                date date NOT NULL,
                startTime time NOT NULL,
                endTime time NOT NULL,
                status varchar(20) NOT NULL DEFAULT 'Pending',
                token varchar(255) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) $charset_collate;",

            'services' => "
            CREATE TABLE {$table_names['services']} (
                id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description TEXT NOT NULL,
                duration time NOT NULL,
                price decimal(10, 2) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY name (name)
            ) $charset_collate;",

            'mapping' => "
            CREATE TABLE {$table_names['mapping']} (
                id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                appointment_id mediumint(9) NOT NULL,
                service_id mediumint(9) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_appointment_service (appointment_id, service_id)
            ) $charset_collate;"
        ];

        foreach ($tables as $table_name => $sql) {
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_names[$table_name]}'") != $table_names[$table_name]) {
                // Table doesn't exist, create it
                dbDelta($sql);
            } else {
                // Table exists, check and update its structure if necessary
                self::update_table_structure($wpdb, $table_names[$table_name], $table_name);
            }
        }
    }

    /**
     * Update the structure of existing tables if necessary.
     *
     * @param \wpdb   $wpdb        WordPress database access abstraction object.
     * @param string  $table_name  Name of the table to update.
     * @param string  $table_key   Key to identify the table structure.
     * @throws \Exception If update fails.
     * @return void
     */
    private static function update_table_structure($wpdb, $table_name, $table_key)
    {
        // Define the expected structure for updates
        $expected_columns = [
            'appointments' => [
                'id',
                'name',
                'surname',
                'phone',
                'email',
                'date',
                'startTime',
                'endTime',
                'status',
                'token',
                'created_at',
                'updated_at'
            ],
            'services' => [
                'id',
                'name',
                'description',
                'duration',
                'price',
                'created_at',
                'updated_at'
            ],
            'mapping' => [
                'id',
                'appointment_id',
                'service_id',
                'created_at'
            ]
        ];

        foreach ($expected_columns[$table_key] as $column) {
            if ($wpdb->get_var("SHOW COLUMNS FROM `{$table_name}` LIKE '{$column}'") === null) {
                // Column does not exist, you can add it as necessary
                switch ($column) {
                    case 'created_at':
                        $wpdb->query("ALTER TABLE `{$table_name}` ADD `created_at` datetime DEFAULT CURRENT_TIMESTAMP");
                        break;
                    case 'updated_at':
                        $wpdb->query("ALTER TABLE `{$table_name}` ADD `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                        break;
                        // Add more cases for other columns as needed
                }
            }
        }
    }

    /**
     * Add foreign key constraints to tables.
     *
     * @since 1.0.0
     * @param \wpdb  $wpdb        WordPress database access abstraction object.
     * @param array  $table_names Array of table names.
     * @throws \Exception If adding foreign keys fails.
     * @return void
     */
    private static function add_foreign_keys($wpdb, $table_names)
    {
        $constraints = [
            'fk_appointment_id' => [
                'table' => $table_names['mapping'],
                'column' => 'appointment_id',
                'reference_table' => $table_names['appointments'],
                'reference_column' => 'id'
            ],
            'fk_service_id' => [
                'table' => $table_names['mapping'],
                'column' => 'service_id',
                'reference_table' => $table_names['services'],
                'reference_column' => 'id'
            ]
        ];

        foreach ($constraints as $constraint_name => $constraint_info) {
            // Check if the constraint already exists
            $constraint_exists = $wpdb->get_var("
            SELECT COUNT(1) constraint_exists
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND CONSTRAINT_NAME = '$constraint_name'
              AND TABLE_NAME = '{$constraint_info['table']}'
        ");

            if ($constraint_exists == 0) {
                // Constraint doesn't exist, so add it
                $query = "ALTER TABLE {$constraint_info['table']}
                ADD CONSTRAINT $constraint_name
                FOREIGN KEY ({$constraint_info['column']})
                REFERENCES {$constraint_info['reference_table']}({$constraint_info['reference_column']})
                ON DELETE CASCADE;";

                $result = $wpdb->query($query);
                if ($result === false) {
                    throw new \Exception('Error executing query: ' . $wpdb->last_error);
                }
            }
        }
    }


    /**
     * Create necessary pages for the plugin.
     *
     * @since 1.0.0
     * @throws \Exception If page creation fails.
     * @return void
     */
    private static function create_pages()
    {
        $pages = [
            ['title' => 'Make an Appointment', 'shortcode' => '[quickappoint_form]'],
            ['title' => 'Appointment Confirmation', 'shortcode' => '[quickappoint_confirmation]']
        ];

        foreach ($pages as $page_info) {
            // Use WP_Query instead of get_page_by_title
            $query = new \WP_Query([
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'title' => $page_info['title']
            ]);

            if (!$query->have_posts()) {
                $page_id = wp_insert_post([
                    'post_title'    => $page_info['title'],
                    'post_content'  => $page_info['shortcode'],
                    'post_status'   => 'publish',
                    'post_type'     => 'page'
                ]);

                if (is_wp_error($page_id)) {
                    throw new \Exception("Failed to create page: {$page_info['title']}. Error: " . $page_id->get_error_message());
                }
            }
        }
    }

    /**
     * Log error messages.
     *
     * @since 1.0.0
     * @param string $message Error message to log.
     * @return void
     */
    private static function log_error($message)
    {
        error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, self::LOG_FILE);
    }
}
