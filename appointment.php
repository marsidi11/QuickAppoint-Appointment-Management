<?php

/**
 * 
 * @package AppointmentManagementPlugin
 * 
 * Plugin Name: Appointments Management
 * Description: This plugin allows you to manage appointments on your WordPress site.
 * Version: 0.8.2
 * Author: Marsid Zyberi
 * Plugin URI: https://marketingon.al/plugins/appointment-management
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
function activate_appointment_management() 
{
    Inc\Base\Activate::activate();
}
function deactivate_appointment_management() 
{
    Inc\Base\Deactivate::deactivate();
}
register_activation_hook( __FILE__, 'activate_appointment_management' ); // Activation
register_deactivation_hook( __FILE__, 'deactivate_appointment_management' ); // Deactivation


// Initialize the core classes of the plugin
if ( class_exists( 'Inc\\Init' ) ) 
{
    Inc\Init::register_services();
}