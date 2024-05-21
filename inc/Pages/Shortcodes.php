<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Pages;

use Inc\Base\BaseController;

/**
 * It creates the shortcode [am_form] and displays the calendar.php template
 * It creates the shortcode [am_confirmation] and displays the calendar.php template
 */
class Shortcodes extends BaseController 
{
    public function register() 
    {
        add_shortcode( 'am_form', array( $this, 'appointment_form' ) );
        add_shortcode( 'am_confirmation', array( $this, 'appointment_confirmation' ) );
    }
    
    public function appointment_form() 
    {
        ob_start(); 
        require_once( $this->plugin_path . 'templates/calendar.php' );
        return ob_get_clean();
    }

    public function appointment_confirmation() 
    {
        ob_start(); 
        require_once( $this->plugin_path . 'templates/confirmation.php' );
        return ob_get_clean();
    }
}