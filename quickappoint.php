<?php

/**
 * 
 * @package AppointmentManagementPlugin
 * 
 * Plugin Name: QuickAppoint - Appointment Management
 * Description: Efficient WordPress scheduling plugin. Optimize time slots, reduce no-shows, and streamline bookings with our user-friendly interface. Simplify appointment management for any business size.
 * Version: 1.0.1
 * Author: Marsid Zyberi
 * Plugin URI: https://marketingon.al/quickappoint-appointment-management-plugin/
 * Text Domain: quickappoint
 * Domain Path: /languages
 */

// Check the script is being executed within the WordPress environment
if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


// Composer autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) 
{
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}


// Activation and deactivation hooks
function activate_quickappoint() 
{
    Inc\Base\Activate::activate();
}
function deactivate_quickappoint() 
{
    Inc\Base\Deactivate::deactivate();
}
register_activation_hook( __FILE__, 'activate_quickappoint' ); // Activation
register_deactivation_hook( __FILE__, 'deactivate_quickappoint' ); // Deactivation


// Initialize the core classes of the plugin
if ( class_exists( 'Inc\\Init' ) ) 
{
    Inc\Init::register_services();
}

// Load the plugin text domain
function quickappoint_load_textdomain() 
{
    load_plugin_textdomain('quickappoint', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'quickappoint_load_textdomain');