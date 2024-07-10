<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Pages;

use Inc\Api\SettingsApi;
use Inc\Base\BaseController;
use Inc\Api\Callbacks\AdminCallbacks;

/**
 * Admin class for the Appointment Management Plugin.
 * 
 * This class registers the plugin through SettingsApi in the WordPress admin menu,
 * adds submenu items, and calls callback methods from AdminCallbacks.
 */
class Admin extends BaseController 
{
    private SettingsApi $settings;
    private AdminCallbacks $callbacks;
    private array $pages = [];
    private array $subpages = [];

    public function register() 
    {
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();

        $this->setPages();
        $this->setSubpages();
        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addPages($this->pages)
                       ->withSubPage('Dashboard')
                       ->addSubPages($this->subpages)
                       ->register();
    }

    private function setPages() 
    {
        $this->pages = [
            [
                'page_title' => 'QuickAppoint - Appointment Management',
                'menu_title' => 'QuickAppoint',
                'capability' => 'manage_options',
                'menu_slug' => 'quickappoint',
                'callback' => [$this->callbacks, 'adminDashboard'],
                'icon_url' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzcyIiBoZWlnaHQ9Ijc3MiIgdmlld0JveD0iMCAwIDc3MiA3NzIiIGZpbGw9IiNhN2FhYWQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0zODYgNzcyQzU5OS4xODIgNzcyIDc3MiA1OTkuMTgyIDc3MiAzODZDNzcyIDE3Mi44MTggNTk5LjE4MiAwIDM4NiAwQzE3Mi44MTggMCAwIDE3Mi44MTggMCAzODZDMCA1OTkuMTgyIDE3Mi44MTggNzcyIDM4NiA3NzJaTTE1NCAxNTRINjE4VjYxOEgxNTRWMTU0Wk0yMDIgMjUwVjU3MEg1NzBWMjUwSDIwMlpNMjI2IDIyNkg1NDZWMjc0SDIyNlYyMjZaIiBmaWxsPSIjYTdhYWFkIi8+CiAgPHRleHQgeD0iMzg2IiB5PSI0ODAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIyNDAiIGZvbnQtd2VpZ2h0PSJib2xkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSJ0cmFuc3BhcmVudCIgc3Ryb2tlPSIjYTdhYWFkIiBzdHJva2Utd2lkdGg9IjgiPjI5PC90ZXh0Pgo8L3N2Zz4=',
                'position' => 2
            ]
        ];
    }

    private function setSubpages() 
    {
        $this->subpages = [
            [
                'parent_slug' => 'quickappoint',
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'manage_options',
                'menu_slug' => 'quickappoint_settings',
                'callback' => [$this->callbacks, 'adminSettings']
            ]
        ];
    }

    private function setSettings() 
    {
        $args = [
            $this->createSettingArg('notifications_email'),
            $this->createSettingArg('currency_symbol'),
            $this->createSettingArg('open_time'),
            $this->createSettingArg('close_time'),
            $this->createSettingArg('time_slot_duration'),
            $this->createSettingArg('buffer_time'),
            $this->createSettingArg('dates_range'),
            $this->createSettingArg('open_days'),
            $this->createSettingArg('break_start'),
            $this->createSettingArg('break_end'),
            $this->createSettingArg('enable_email_confirmation'),
            $this->createSettingArg('background_color'),
            $this->createSettingArg('primary_color'),
            $this->createSettingArg('secondary_color'),
            $this->createSettingArg('text_color'),
        ];

        $this->settings->setSettings($args);
    }

    private function createSettingArg(string $optionName): array
    {
        return [
            'option_group' => 'am_options_data',
            'option_name' => $optionName,
            'callback' => [$this->callbacks, 'amOptionsData']
        ];
    }

    private function setSections() 
    {
        $args = [
            [
                'id' => 'am_admin_index',
                'title' => 'Settings',
                'callback' => [$this->callbacks, 'amAdminSection'],
                'page' => 'quickappoint'
            ]
        ];

        $this->settings->setSections($args);
    }

    private function setFields() 
    {
        $args = [
            $this->createFieldArg('notifications_email', 'Notifications Email (, multipe)', 'amNotificationsEmail', 'notifications-email'),
            $this->createFieldArg('currency_symbol', 'Currency Symbol ($)', 'amCurrencySymbol', 'currency-symbol'),
            $this->createFieldArg('open_time', 'Open Time', 'amOpenTime', 'select-time'),
            $this->createFieldArg('close_time', 'Close Time', 'amCloseTime', 'select-time'),
            $this->createFieldArg('time_slot_duration', 'Time Slot Duration (every x minutes)', 'amTimeSlotDuration', 'select-time'),
            $this->createFieldArg('buffer_time', 'Buffer Time', 'amBufferTime', 'select-time'),
            $this->createFieldArg('dates_range', 'Allowed Bookings Date Range', 'amDatesRange', 'select-time'),
            $this->createFieldArg('open_days', 'Open Days', 'amOpenDays', 'select-allowed-dates'),
            $this->createFieldArg('break_start', 'Break Start', 'amBreakStart', 'select-time'),
            $this->createFieldArg('break_end', 'Break End', 'amBreakEnd', 'select-time'),
            $this->createFieldArg('background_color', 'Background Color', 'amBackgroundColor', 'select-color'),
            $this->createFieldArg('primary_color', 'Primary Color', 'amPrimaryColor', 'select-color'),
            $this->createFieldArg('secondary_color', 'Secondary Color', 'amSecondaryColor', 'select-color'),
            $this->createFieldArg('text_color', 'Text Color', 'amTextColor', 'select-color'),
        ];

        $this->settings->setFields($args);
    }

    private function createFieldArg(string $id, string $title, string $callback, string $class): array
    {
        return [
            'id' => $id,
            'title' => __($title, 'appointment-management'),
            'callback' => [$this->callbacks, $callback],
            'page' => 'quickappoint',
            'section' => 'am_admin_index',
            'args' => [
                'label_for' => $id,
                'class' => $class
            ]
        ];
    }
}