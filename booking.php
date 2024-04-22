<?php

/**
 * 
 * @package BookingManagementPlugin
 * 
 * Plugin Name: Booking Management
 * Description: This plugin allows you to manage bookings on your WordPress site.
 * Version: 1.0.0
 * Author: Marsid Zyberi
 * Plugin URI: https://marketingon.al/plugins/booking_management
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
function activate_booking_management() 
{
    Inc\Base\Activate::activate();
}
function deactivate_booking_management() 
{
    Inc\Base\Deactivate::deactivate();
}
register_activation_hook( __FILE__, 'activate_booking_management' ); // Activation
register_deactivation_hook( __FILE__, 'deactivate_booking_management' ); // Deactivation


// Initialize the core classes of the plugin
if ( class_exists( 'Inc\\Init' ) ) 
{
    Inc\Init::register_services();
}