<?php
/**
 * @package AppointmentManagementPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

/**
 * AdminCallbacks class containing callback methods for various admin actions.
 */
class AdminCallbacks extends BaseController
{
    /**
     * Renders the dashboard page.
     *
     * @return void
     */
    public function adminDashboard(): void
    {
        require_once "{$this->plugin_path}/templates/dashboard.php";
    }

    /**
     * Renders the settings page.
     *
     * @return void
     */
    public function adminSettings(): void
    {
        require_once "{$this->plugin_path}/templates/settings.php";
    }

    /**
     * Callback for register_setting.
     *
     * @param mixed $input The input value.
     * @return mixed The input value unchanged.
     */
    public function amOptionsData($input)
    {
        return $input;
    }

    /**
     * Renders the admin section description.
     *
     * @return void
     */
    public function amAdminSection(): void
    {
        echo 'Select your options below!';
    }

    /**
     * Renders the currency symbol input field.
     *
     * @return void
     */
    public function amCurrencySymbol(): void
    {
        $this->renderInputField('currency_symbol', 'text', '$', 'Currency Symbol');
    }

    /**
     * Renders the open time input field.
     *
     * @return void
     */
    public function amOpenTime(): void
    {
        $this->renderInputField('open_time', 'time', '09:00', 'Select start time');
    }

    /**
     * Renders the close time input field.
     *
     * @return void
     */
    public function amCloseTime(): void
    {
        $this->renderInputField('close_time', 'time', '17:00', 'Select close time');
    }

    /**
     * Renders the time slot duration input field.
     *
     * @return void
     */
    public function amTimeSlotDuration(): void
    {
        $this->renderInputField('time_slot_duration', 'text', '30', 'Time Slot Duration');
    }

    /**
     * Renders the dates range input field.
     *
     * @return void
     */
    public function amDatesRange(): void
    {
        $this->renderInputField('dates_range', 'text', '21', 'Dates Range to Allow Bookings');
    }

    /**
     * Renders the open days checkboxes.
     *
     * @return void
     */
    public function amOpenDays(): void
    {
        $value = get_option('open_days', []);
        $days = [
            'monday'    => __('Monday', 'appointment-management'),
            'tuesday'   => __('Tuesday', 'appointment-management'),
            'wednesday' => __('Wednesday', 'appointment-management'),
            'thursday'  => __('Thursday', 'appointment-management'),
            'friday'    => __('Friday', 'appointment-management'),
            'saturday'  => __('Saturday', 'appointment-management'),
            'sunday'    => __('Sunday', 'appointment-management'),
        ];

        foreach ($days as $day_value => $day_label) {
            $checked = in_array($day_value, $value) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="open_days[]" value="' . esc_attr($day_value) . '" ' . $checked . '> ' . esc_html($day_label) . '</label><br>';
        }
    }

    /**
     * Renders the break start time input field.
     *
     * @return void
     */
    public function amBreakStart(): void
    {
        $this->renderInputField('break_start', 'time', '', 'Select start time');
    }

    /**
     * Renders the break end time input field.
     *
     * @return void
     */
    public function amBreakEnd(): void
    {
        $this->renderInputField('break_end', 'time', '', 'Select close time');
    }

    /**
     * Renders the primary color input field.
     *
     * @return void
     */
    public function amPrimaryColor(): void
    {
        $this->renderColorField('primary_color', '#007bff', 'Select primary color');
    }

    /**
     * Renders the secondary color input field.
     *
     * @return void
     */
    public function amSecondaryColor(): void
    {
        $this->renderColorField('secondary_color', '#6c757d', 'Select secondary color');
    }

    /**
     * Helper method to render input fields.
     *
     * @param string $name The name of the input field.
     * @param string $type The type of the input field.
     * @param string $default The default value.
     * @param string $placeholder The placeholder text.
     * @return void
     */
    private function renderInputField(string $name, string $type, string $default, string $placeholder): void
    {
        $value = esc_attr(get_option($name, $default));
        echo "<input type=\"$type\" class=\"regular-text\" name=\"$name\" value=\"$value\" placeholder=\"$placeholder\">";
    }

    /**
     * Helper method to render color input fields.
     *
     * @param string $name The name of the input field.
     * @param string $default The default color value.
     * @param string $label The label for the input field.
     * @return void
     */
    private function renderColorField(string $name, string $default, string $label): void
    {
        $value = esc_attr(get_option($name, $default));
        echo "<label for='$name'>$label:</label><br>";
        echo "<input type='color' id='$name' name='$name' value='$value' style='width: 50px; height: 50px; vertical-align: middle;'>";
        echo "<input type='text' id='{$name}_text' name='{$name}_text' value='$value' style='width: 120px; margin-left: 10px;' placeholder='Hex or RGB'>";
        
        // Add JavaScript to handle color input and conversion
        echo "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var colorPicker = document.getElementById('$name');
            var textInput = document.getElementById('{$name}_text');
            
            function isValidColor(c                return /^#[0-9A-F]{6}$/i.test(color) || 
                       /^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/i.test(color);
            }

            function rgbToHex(r, g, b) {
                return '#' + [r, g, b].map(x => {
                    const hex =                    return hex.length === 1 ? '0' + hex : hex;
                }).join('');
            }

            function hexToRgb(hex) {
                var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? 
                    `rgb(                    : null;
            }

            colorPicker.addEventListener('input', function() {
                textInput.value = this.value;
            });
            
                   var color = this.value.trim();
                if (isValidColor(color)) {
                    if (color.startsWith('rgb')) {
                        var rgb = color.match(/\d+/g);
                        colorPicker.value = rgbToHex(parseInt(rgb[0]), parseInt(rgb[1]), parseInt(rgb[2]));
                    } else {
                        colorPicker.value = color;
                    }
                }
            });

            textInput.addEventListener('blur', function() {
                var color = this.value.trim();
                if (isValidColor(color                    if (color.startsWith('#')) {
                        this.value = color.toUpperCase();
                    } else if (color.startsWith('rgb')) {
                        var rgb = color.match(/\d+/g);
                        this.value = rgbToHex(parseInt(rgb[0]), parseInt(rgb[1]), parseInt(rgb[2])).toUpperCase();
                    }
                } else {
                    this.value = colorPicker.value.toUpperCase();
                }
            });
        });
        </script>
        ";
    }

}