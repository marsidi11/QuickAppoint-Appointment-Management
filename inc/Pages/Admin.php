<?php
/**
 * @package AppointmentManagementPlugin
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
                'page_title' => 'Appointment Management', 
                'menu_title' => 'Appointment', 
                'capability' => 'manage_options', 
                'menu_slug' => 'appointment_management', 
                'callback' => array( $this->callbacks, 'adminDashboard' ), // Callback to the dashboard page
                'icon_url' => $this->plugin_url . 'assets/img/admin_icon.png', 
                'position' => 2
            )
        );
    }

    public function setSubpages() 
    {
        $this->subpages = array(
            // array(
            //     'parent_slug' => 'appointment_management', 
            //     'page_title' => 'Settings', 
            //     'menu_title' => 'Settings', 
            //     'capability' => 'manage_options', 
            //     'menu_slug' => 'appointment_management_settings', 
            //     'callback' => array( $this->callbacks, 'adminSettings' ) // Callback to the settings page
            // )
        );
    }

    // Register custom Settings setters for custom option data
    public function setSettings() 
    {
        $args = array(

            // Example Options Settings Page
            array(
                'option_group' => 'appointment_management_option_group',
                'option_name' => 'text_example',
                'callback' => array( $this->callbacks, 'appointmentManagementOptionsGroup' 
                )
            ),
            array(
                'option_group' => 'appointment_management_option_group',
                'option_name' => 'first_name'
            ),

            // Open Time, Close Time, Time Break, Allowed Dates Range, Open Days, Start Break Time, Close Break Time - Options Dashboard Page
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'open_time',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'close_time',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'time_slot_duration',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'dates_range',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'open_days',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'break_start',
                'callback' => array( $this->callbacks, 'amOptionsData' 
                )
            ),
            array(
                'option_group' => 'am_options_data',
                'option_name' => 'break_end',
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
                'id' => 'appointment_management_admin_index',
                'title' => 'Settings Example',
                'callback' => array( $this->callbacks, 'appointmentManagementAdminSection' ),
                'page' => 'appointment_management_settings'
            ),

            // Open Time, Close Time, Time Break, Allowed Dates Range, Open Days, Start Break Time, Close Break Time - Sections
            array(
                'id' => 'am_admin_index',
                'title' => 'Settings',
                'callback' => array( $this->callbacks, 'amAdminSection' ),
                'page' => 'appointment_management'
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
                'callback' => array( $this->callbacks, 'appointmentManagementTextExample' ),
                'page' => 'appointment_management_settings',
                'section' => 'appointment_management_admin_index',
                'args' => array(
                    'label_for' => 'text_example',
                    'class' => 'example-class'
                )
            ),
            array(
                'id' => 'first_name',
                'title' => 'First Name',
                'callback' => array( $this->callbacks, 'appointmentManagementFirstName' ),
                'page' => 'appointment_management_settings',
                'section' => 'appointment_management_admin_index',
                'args' => array(
                    'label_for' => 'first_name',
                    'class' => 'example-class'
                )
            ),

            // Open Time, Close Time, Time Break, Allowed Dates Range, Open Days, Start Break Time, Close Break Time - Fields
            array(
                'id' => 'open_time',
                'title' => __('Open Time', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amOpenTime' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'open_time',
                    'class' => 'select-time'
                )
            ),
            array(
                'id' => 'close_time',
                'title' => __('Close Time', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amCloseTime' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'close_time',
                    'class' => 'select-time'
                )
            ),
            array(
                'id' => 'time_slot_duration',
                'title' => __('Time Slot Duration (every x minutes)', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amTimeSlotDuration' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'time_slot_duration',
                    'class' => 'select-time'
                )
            ),
            array(
                'id' => 'dates_range',
                'title' => __('Allowed Bookings Date Range', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amDatesRange' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'dates_range',
                    'class' => 'select-allowed-dates'
                )
            ),
            array(
                'id' => 'open_days',
                'title' => __('Open Days', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amOpenDays' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'open_days',
                    'class' => 'select-allowed-dates'
                )
            ),
            array(
                'id' => 'break_start',
                'title' => __('Break Start', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amBreakStart' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'break_start',
                    'class' => 'select-time'
                )
            ),
            array(
                'id' => 'break_end',
                'title' => __('Break End', 'appointment-management'),
                'callback' => array( $this->callbacks, 'amBreakEnd' ),
                'page' => 'appointment_management',
                'section' => 'am_admin_index',
                'args' => array(
                    'label_for' => 'break_end',
                    'class' => 'select-time'
                )
            )
        );

        $this->settings->setFields( $args );
    }

}