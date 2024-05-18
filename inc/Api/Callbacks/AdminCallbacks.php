<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

/**
 * It contains callback methods for various admin actions.
 * 
 * Methods:
 * - adminDashboard: Returns the dashboard page.
 * - adminSettings: Returns the settings page.
 * - appointmentManagementOptionsGroup: Returns the input as is. This can be used as a callback for register_setting.
 * - appointmentManagementAdminSection: Echoes a string for the admin section.
 * - appointmentManagementTextExample: Echoes an input field for a text example option.
 * - appointmentManagementFirstName: Echoes an input field for a first name option.
 * 
 * Each method is intended to be used as a callback function in the WordPress settings API.
 */
class AdminCallbacks extends BaseController
{
    // This method calls the dashboard page
    public function adminDashboard() 
    {
        return require_once( "$this->plugin_path/templates/dashboard.php" );
    }

    // This method calls the settings page
    public function adminSettings() 
    {
        return require_once( "$this->plugin_path/templates/settings.php" );
    }

    // Register Custom Fields Options
    public function appointmentManagementOptionsGroup( $input ) 
    {
        return $input;
    }

    public function amOptionsData( $input ) 
    {
        return $input;
    }

    public function appointmentManagementAdminSection() 
    {
        echo 'Check this example section!';
    }

    public function amAdminSection() 
    {
        echo 'Select your options below!';
    }

    // Settings callback functions
    public function amCurrencySymbol() 
    {
        $value = esc_attr( get_option( 'currency_symbol', '$' ) );
        echo '<input type="text" class="regular-text" name="currency_symbol" value="' . $value . '" placeholder="Currency Symbol">';
    }

    public function amOpenTime() 
    {
        $value = esc_attr( get_option( 'open_time', '09:00' ) );
        echo '<input type="time" class="regular-text" name="open_time" value="' . $value . '" placeholder="Select start time">';
    }
    // TODO: Check if close time is later than open time
    public function amCloseTime() 
    {
        $value = esc_attr( get_option( 'close_time', '17:00' ) );
        echo '<input type="time" class="regular-text" name="close_time" value="' . $value . '" placeholder="Select close time">';
    }

    public function amTimeSlotDuration() 
    {
        $value = esc_attr( get_option( 'time_slot_duration', '30' ) );
        echo '<input type="text" class="regular-text" name="time_slot_duration" value="' . $value . '" placeholder="Time Slot Duration">';
    }

    public function amDatesRange() 
    {
        $value = esc_attr( get_option( 'dates_range', '21' ) );
        echo '<input type="text" class="regular-text" name="dates_range" value="' . $value . '" placeholder="Dates Range to Allow Bookings">';
    }

    public function amOpenDays()
    {
        $value = get_option('open_days', array());
        $days = array(
            'monday'    => __('Monday', 'appointment-management'),
            'tuesday'   => __('Tuesday', 'appointment-management'),
            'wednesday' => __('Wednesday', 'appointment-management'),
            'thursday'  => __('Thursday', 'appointment-management'),
            'friday'    => __('Friday', 'appointment-management'),
            'saturday'  => __('Saturday', 'appointment-management'),
            'sunday'    => __('Sunday', 'appointment-management'),
        );

        foreach ($days as $day_value => $day_label) 
        {
            $checked = in_array($day_value, $value) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="open_days[]" value="' . esc_attr($day_value) . '" ' . $checked . '> ' . esc_html($day_label) . '</label><br>';
        }
    }

    public function amBreakStart() 
    {
        $value = esc_attr( get_option( 'break_start') );
        echo '<input type="time" class="regular-text" name="break_start" value="' . $value . '" placeholder="Select start time">';
    }
    // TODO: Check if break end time is later than break start time
    public function amBreakEnd() 
    {
        $value = esc_attr( get_option( 'break_end') );
        echo '<input type="time" class="regular-text" name="break_end" value="' . $value . '" placeholder="Select close time">';
    }

    public function amEnableEmailVerification() 
    {
        $value = esc_attr( get_option( 'enable_email_verification', '0' ) );
        $checked = $value === '1' ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="enable_email_verification" value="1" ' . $checked . '> Enable</label>';
    }
}