<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Pages;

use \Inc\Api\SettingsApi;
use \Inc\Base\BaseController;
use \Inc\Api\Callbacks\AdminCallbacks;

/**
 * It registers the plugin through SettingsApi in the WordPress admin menu. 
 * It adds the submenu items. 
 * It calls the callback methods from AdminCallbacks.
 */
class Admin extends BaseController 
{
    public $settings;

    public $callbacks;

    public $pages = array();

    public $subpages = array();

    public function register() 
    {
        $this->settings = new SettingsApi();

        $this->callbacks = new AdminCallbacks();

        $this->setPages();
        $this->setSubpages();

        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addPages( $this->pages )->withSubPage( 'Dashboard' )->addSubPages( $this->subpages )->register();
    }

    public function setPages() 
    {
        $this->pages = array(
            array(
                'page_title' => 'Booking Management', 
                'menu_title' => 'Booking', 
                'capability' => 'manage_options', 
                'menu_slug' => 'booking_management', 
                'callback' => array( $this->callbacks, 'adminDashboard' ), // Callback to the dashboard page
                'icon_url' => $this->plugin_url . 'assets/img/admin_icon.png', 
                'position' => 2
            )
        );
    }

    public function setSubpages() 
    {
        $this->subpages = array(
            array(
                'parent_slug' => 'booking_management', 
                'page_title' => 'Settings', 
                'menu_title' => 'Settings', 
                'capability' => 'manage_options', 
                'menu_slug' => 'booking_management_settings', 
                'callback' => array( $this->callbacks, 'adminSettings' ) // Callback to the settings page
            ),
            array(
                'parent_slug' => 'booking_management', 
                'page_title' => 'Custom Submenu', 
                'menu_title' => 'Custom Submenu', 
                'capability' => 'manage_options', 
                'menu_slug' => 'booking_management_submenu', 
                'callback' => function() { echo '<h1>Custom Submenu</h1>'; }
            )
        );
    }

    // Register custom Settings setters for custom option data
    public function setSettings() 
    {
        $args = array(

            // Example Options Settings Page
            array(
                'option_group' => 'booking_management_option_group',
                'option_name' => 'text_example',
                'callback' => array( $this->callbacks, 'bookingManagementOptionsGroup' 
                )
            ),
            array(
                'option_group' => 'booking_management_option_group',
                'option_name' => 'first_name'
            ),

            // Start Time and End Time Options Dashboard Page
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'start_time',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'end_time',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            )
        );

        $this->settings->setSettings( $args );
    }

    // Register custom Sections setters
    public function setSections() 
    {
        $args = array(

            // Example Sections
            array(
                'id' => 'booking_management_admin_index',
                'title' => 'Settings Example',
                'callback' => array( $this->callbacks, 'bookingManagementAdminSection' ),
                'page' => 'booking_management_settings'
            ),

            // Start Time and End Time sections
            array(
                'id' => 'am_admin_index',
                'title' => 'Settings',
                'callback' => array( $this->callbacks, 'amAdminSection' ),
                'page' => 'booking_management'
            )
        );

        $this->settings->setSections( $args );
    }

    // Register custom Fields setters
    public function setFields() 
    {
        
        $args = array(

            // Example Fields
            array(
                'id' => 'text_example',
                'title' => 'Text Example',
                'callback' => array( $this->callbacks, 'bookingManagementTextExample' ),
                'page' => 'booking_management_settings',
                'section' => 'booking_management_admin_index',
                'args' => array(
                    'label_for' => 'text_example',
                    'class' => 'example-class'
                )
            ),
            array(
                'id' => 'first_name',
                'title' => 'First Name',
                'callback' => array( $this->callbacks, 'bookingManagementFirstName' ),
                'page' => 'booking_management_settings',
                'section' => 'booking_management_admin_index',
                'args' => array(
                    'label_for' => 'first_name',
                    'class' => 'example-class'
                )
            ),

            // Start Time and End Time Fields
            array(
                'id' => 'start_time',
                'title' => 'Start Time',
                'callback' => array( $this->callbacks, 'amStartTime' ),
                'page' => 'booking_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'start_time',
                    'class' => 'example-class'
                )
            ),
            array(
                'id' => 'end_time',
                'title' => 'End Time',
                'callback' => array( $this->callbacks, 'amEndTime' ),
                'page' => 'booking_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'end_time',
                    'class' => 'example-class'
                )
            )
        );

        $this->settings->setFields( $args );
    }

}