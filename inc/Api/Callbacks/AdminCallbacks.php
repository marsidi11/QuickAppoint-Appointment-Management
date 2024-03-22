<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;


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

    // Register Custom Fields
    public function bookingManagementOptionsGroup( $input ) 
    {
        return $input;
    }

    public function bookingManagementAdminSection() 
    {
        echo 'Check this section!';
    }

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
}