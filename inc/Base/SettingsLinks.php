<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;

Class SettingsLinks extends BaseController {

    public function register() {
        
        add_filter('plugin_action_links_' . $this->plugin, [$this, 'settings_link']); // Add settings link to plugin
    }

    public function settings_link( $links ) {
            $settings_link = '<a href="admin.php?page=booking_management-settings">Settings</a>';
            array_push($links, $settings_link);
            return $links;
        }
}