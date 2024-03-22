<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Pages;

use \Inc\Api\SettingsApi;
use \Inc\Base\BaseController;
use \Inc\Api\Callbacks\AdminCallbacks;

/**
 * It registers the plugin in the WordPress admin menu, also it adds the submenu items.
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
                'menu_slug' => 'booking-management', 
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
                'parent_slug' => 'booking-management', 
                'page_title' => 'Settings', 
                'menu_title' => 'Settings', 
                'capability' => 'manage_options', 
                'menu_slug' => 'booking-management-settings', 
                'callback' => array( $this->callbacks, 'adminSettings' ) // Callback to the settings page
            ),
            array(
                'parent_slug' => 'booking-management', 
                'page_title' => 'Custom Submenu', 
                'menu_title' => 'Custom Submenu', 
                'capability' => 'manage_options', 
                'menu_slug' => 'booking-management-submenu', 
                'callback' => function() { echo '<h1>Custom Submenu</h1>'; }
            )
        );
    }

    // Register custom fields setters

    public function setSettings() 
    {
        $args = array(
            array(
                'option_group' => 'booking_management_option_group',
                'option_name' => 'text_example',
                'callback' => array( $this->callbacks, 'bookingManagementOptionsGroup' 
                )
            ),
            array(
                'option_group' => 'booking_management_option_group',
                'option_name' => 'first_name'
            )
        );

        $this->settings->setSettings( $args );
    }

    public function setSections() 
    {
        $args = array(
            array(
                'id' => 'booking_management_admin_index',
                'title' => 'Settings',
                'callback' => array( $this->callbacks, 'bookingManagementAdminSection' ),
                'page' => 'booking_management'
            )
        );

        $this->settings->setSections( $args );
    }

    public function setFields() 
    {
        $args = array(
            array(
                'id' => 'text_example',
                'title' => 'Text Example',
                'callback' => array( $this->callbacks, 'bookingManagementTextExample' ),
                'page' => 'booking_management',
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
                'page' => 'booking_management',
                'section' => 'booking_management_admin_index',
                'args' => array(
                    'label_for' => 'first_name',
                    'class' => 'example-class'
                )
            )
                
        );

        $this->settings->setFields( $args );
    }

}