<?php
/**
 * Color Generator for WordPress Plugin
 * 
 * This file handles the generation of color shades based on primary and secondary colors
 * stored in WordPress options.
 */

namespace Inc\Api\Callbacks;

class ColorGenerator {
    /**
     * Generate CSS variables for color shades
     *
     * @return string CSS with color variables
     */
    public static function generate_color_variables() {
        $primary_color = get_option('primary_color', '#4b5563');
        $secondary_color = get_option('secondary_color', '#3b82f6');

        $primary_shades = self::generate_color_shades($primary_color);
        $secondary_shades = self::generate_color_shades($secondary_color);

        $css = ":root {\n";
        foreach ($primary_shades as $key => $value) {
            $css .= "  --primary-color-{$key}: {$value};\n";
        }
        foreach ($secondary_shades as $key => $value) {
            $css .= "  --secondary-color-{$key}: {$value};\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate color shades
     *
     * @param string $base_color Base color in hexadecimal format
     * @return array Array of color shades
     */
    public static function generate_color_shades($base_color) {
        return [
            '50' => self::lighten_color($base_color, 50),
            '100' => self::lighten_color($base_color, 40),
            '200' => self::lighten_color($base_color, 30),
            '300' => self::lighten_color($base_color, 20),
            '400' => self::lighten_color($base_color, 10),
            '500' => self::lighten_color($base_color, 5),
            '600' => $base_color,
            '700' => self::darken_color($base_color, 10),
            '800' => self::darken_color($base_color, 20),
            '900' => self::darken_color($base_color, 30),
            '950' => self::darken_color($base_color, 40),
        ];
    }

    /**
     * Lighten a color
     *
     * @param string $hex Hexadecimal color
     * @param int $percent Percentage to lighten
     * @return string Lightened color in hexadecimal format
     */
    private static function lighten_color($hex, $percent) {
        return self::adjust_color($hex, $percent);
    }

    /**
     * Darken a color
     *
     * @param string $hex Hexadecimal color
     * @param int $percent Percentage to darken
     * @return string Darkened color in hexadecimal format
     */
    private static function darken_color($hex, $percent) {
        return self::adjust_color($hex, -$percent);
    }

    /**
     * Adjust color lightness
     *
     * @param string $hex Hexadecimal color
     * @param int $percent Percentage to adjust (positive to lighten, negative to darken)
     * @return string Adjusted color in hexadecimal format
     */
    private static function adjust_color($hex, $percent) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $hex = array_map('hexdec', str_split($hex, 2));
        
        foreach ($hex as &$color) {
            $adjustAmount = ceil($color * $percent / 100);
            $color = str_pad(dechex(min(255, max(0, $color + $adjustAmount))), 2, '0', STR_PAD_LEFT);
        }
        
        return '#' . implode('', $hex);
    }
}