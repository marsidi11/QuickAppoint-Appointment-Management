<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;

/**
 * Enqueue scripts and styles 
 */
class Enqueue extends BaseController 
{
    public function register() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts' ) ); // Enqueue scripts for back-end
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) ); // Enqueue scripts for front-end
    }

    public function enqueue_backend_scripts() {
        wp_enqueue_style( 'booking-management-backend-style', $this->plugin_url . 'assets/css/dist/backend.styles.css' );
        wp_enqueue_script( 'booking-management-backend-script', $this->plugin_url . 'assets/js/dist/backend.bundle.js' );
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_style( 'booking-management-frontend-style', $this->plugin_url . 'assets/css/dist/frontend.styles.css' );
        wp_enqueue_script( 'booking-management-frontend-script', $this->plugin_url . 'assets/js/dist/frontend.bundle.js' );
    }
}