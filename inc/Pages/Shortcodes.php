<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Pages;

use Inc\Base\BaseController;

/**
 * It creates the shortcode [appointment_formm] and displays the calendar.php template
 */
class Shortcodes extends BaseController 
{
    public function register() 
    {
        add_shortcode( 'appointment_formm', array( $this, 'appointment_form' ) );
    }
    
    public function appointment_form() 
    {
        ob_start(); 
        require_once( $this->plugin_path . 'templates/calendar.php' );
        return ob_get_clean();
    }
}