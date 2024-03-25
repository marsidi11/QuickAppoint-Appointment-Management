<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Pages;

use Inc\Base\BaseController;

/**
 * It creates the shortcode [booking_formm] and displays the calendar.php template
 */
class Shortcodes extends BaseController 
{
    public function register() 
    {
        add_shortcode( 'booking_formm', array( $this, 'booking_form' ) );
    }
    
    public function booking_form() 
    {
        ob_start(); 
        require_once( $this->plugin_path . 'templates/calendar.php' );
        return ob_get_clean();
    }
}