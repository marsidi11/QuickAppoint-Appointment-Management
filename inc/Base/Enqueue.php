<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;

class Enqueue extends BaseController {

    public function register() {
        add_action( 'admin_enqueue_scripts', array( $this,'enqueue_scripts' )); // Enqueue scripts
    }
    function enqueue_scripts() {
        wp_enqueue_style( 'booking-management-style', $this->plugin_url . 'assets/css/dist/styles.css' );
        wp_enqueue_script( 'booking-management-script', $this->plugin_url . 'assets/js/dist/main.bundle.js' );
    }
}