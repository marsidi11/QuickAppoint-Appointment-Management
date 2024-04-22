<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

/**
 * It contains callback methods for various admin actions.
 * 
 * Methods:
 * - adminDashboard: Returns the dashboard page.
 * - adminSettings: Returns the settings page.
 * - bookingManagementOptionsGroup: Returns the input as is. This can be used as a callback for register_setting.
 * - bookingManagementAdminSection: Echoes a string for the admin section.
 * - bookingManagementTextExample: Echoes an input field for a text example option.
 * - bookingManagementFirstName: Echoes an input field for a first name option.
 * 
 * Each method is intended to be used as a callback function in the WordPress settings API.
 */
class AdminCallbacks extends BaseController
{
    // This method calls the dashboard page
    public function adminDashboard() 
    {
        return require_once( "$this->plugin_path/templates/dashboard.php" );
    }

    // This method calls the settings page
    public function adminSettings() 
    {
        return require_once( "$this->plugin_path/templates/settings.php" );
    }

    // Register Custom Fields Options
    public function bookingManagementOptionsGroup( $input ) 
    {
        return $input;
    }

    public function amOptionsData( $input ) 
    {
        return $input;
    }

    public function bookingManagementAdminSection() 
    {
        echo 'Check this example section!';
    }

    public function amAdminSection() 
    {
        echo 'Select your options below!';
    }

    // Example callback functions
    public function bookingManagementTextExample() 
    {
        $value = esc_attr( get_option( 'text_example' ) );
        echo '<input type="text" class="regular-text" name="text_example" value="' . $value . '" placeholder="Write something here!">';
    }

    public function bookingManagementFirstName() 
    {
        $value = esc_attr( get_option( 'first_name' ) );
        echo '<input type="text" class="regular-text" name="first_name" value="' . $value . '" placeholder="Write your first name here!">';
    }

    // Settings callback functions
    public function amStartTime() 
    {
        $value = esc_attr( get_option( 'start_time', '09:00' ) );
        echo '<input type="time" class="regular-text" name="start_time" value="' . $value . '" placeholder="Select start time">';
    }

    public function amEndTime() 
    {
        $value = esc_attr( get_option( 'end_time', '17:00' ) );
        echo '<input type="time" class="regular-text" name="end_time" value="' . $value . '" placeholder="Select end time">';
    }
}