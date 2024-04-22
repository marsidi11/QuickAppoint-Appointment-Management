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
    public function register() 
    {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts' ) ); // Enqueue scripts for admin-side
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) ); // Enqueue scripts for front-end
    }


    public function enqueue_backend_scripts() 
    {
        wp_enqueue_style( 'booking_management-backend-style', $this->plugin_url . 'assets/dist/backend.styles.css' );
        wp_enqueue_script( 'booking_management-backend-script', $this->plugin_url . 'assets/dist/backend.bundle.js' );

        // Create a nonce
        $nonce = wp_create_nonce('wp_rest');

        // Localize the script with your data.
        $data = array(
                'nonce' => $nonce,
                'apiUrlAppointments' => esc_url_raw( rest_url('booking_management/v1/appointments') ),
                'apiUrlServices' => esc_url_raw( rest_url('booking_management/v1/services') ),
            );
        wp_localize_script('booking_management-backend-script', 'wpApiSettings', $data);
    }
    

    public function enqueue_frontend_scripts() 
    {
        wp_enqueue_style( 'booking_management-frontend-style', $this->plugin_url . 'assets/dist/frontend.styles.css' );
        wp_enqueue_script( 'booking_management-frontend-script', $this->plugin_url . 'assets/dist/frontend.bundle.js' );

        // Create a nonce
        $nonce = wp_create_nonce('wp_rest');

        // Localize the script with your data.
        $data = array(
                'nonce' => $nonce,
                'apiUrlAppointments' => esc_url_raw( rest_url('booking_management/v1/appointments') ),
                'apiUrlServices' => esc_url_raw( rest_url('booking_management/v1/services') ),
            );
        wp_localize_script('booking_management-frontend-script', 'wpApiSettings', $data);

    }
}