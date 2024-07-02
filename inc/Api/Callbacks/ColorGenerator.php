<?php
/**
 * Color Generator for WordPress Plugin
 * 
 * This file handles the generation of color shades based on primary and secondary colors and tertiary color
 * stored in WordPress options. It uses the HSL color space for more visually appealing results.
 */

namespace Inc\Api\Callbacks;

class ColorGenerator {
    /**
     * Generate CSS variables for color shades
     *
     * This function retrieves the primary, secondary, and tertiary colors from WordPress options,
     * generates shades for each, and returns a CSS string with color variables.
     *
     * @return string CSS with color variables
     */
    public static function generate_color_variables() {
        $background_color = get_option('background_color', '#ffffff');
        $primary_color = get_option('primary_color', '#4b5563');
        $secondary_color = get_option('secondary_color', '#3b82f6');
        $tertiary_color = get_option('text_color', '#272525');

        $background_shades = self::generate_color_shades($background_color);
        $primary_shades = self::generate_color_shades($primary_color);
        $secondary_shades = self::generate_color_shades($secondary_color);
        $tertiary_shades = self::generate_color_shades($tertiary_color);
        
        $css = ":root {\n";
        foreach ($background_shades as $key => $value) {
            $css .= "  --background-color-{$key}: {$value};\n";
        }
        foreach ($primary_shades as $key => $value) {
            $css .= "  --primary-color-{$key}: {$value};\n";
        }
        foreach ($secondary_shades as $key => $value) {
            $css .= "  --secondary-color-{$key}: {$value};\n";
        }
        foreach ($tertiary_shades as $key => $value) {
            $css .= "  --tertiary-color-{$key}: {$value};\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate color shades
     *
     * This function takes a base color and generates a range of shades from very light to very dark.
     * It uses the HSL color space to maintain the hue and saturation while adjusting the lightness.
     *
     * @param string $base_color Base color in hexadecimal format
     * @return array Array of color shades
     */
    public static function generate_color_shades($base_color) {
        $hsl = self::hex_to_hsl($base_color);
        $shades = [];

        // The default lightness value from the base color is used for the 600 shade
        $default_lightness = $hsl[2];

        $lightness_steps = [
            '50' => max($default_lightness + 0.45, 1.0), '100' => max($default_lightness + 0.4, 0.95), '200' => max($default_lightness + 0.3, 0.85), '300' => max($default_lightness + 0.2, 0.75), '400' => max($default_lightness + 0.1, 0.65),
            '500' => max($default_lightness + 0.05, 0.55), '600' => $default_lightness, '700' => max($default_lightness - 0.1, 0.35), '800' => max($default_lightness - 0.2, 0.25), '900' => max($default_lightness - 0.3, 0.15), '950' => max($default_lightness - 0.35, 0.1)
        ];

        foreach ($lightness_steps as $key => $lightness) {
            $shades[$key] = self::hsl_to_hex([
                $hsl[0],
                $hsl[1],
                $lightness
            ]);
        }

        return $shades;
    }

    /**
     * Convert hexadecimal color to HSL
     *
     * This function takes a color in hexadecimal format and converts it to HSL (Hue, Saturation, Lightness).
     *
     * @param string $hex Hexadecimal color
     * @return array Array containing H, S, and L values
     */
    private static function hex_to_hsl($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
                case $g: $h = ($b - $r) / $d + 2; break;
                case $b: $h = ($r - $g) / $d + 4; break;
            }
            $h /= 6;
        }

        return [$h, $s, $l];
    }

    /**
     * Convert HSL color to hexadecimal
     *
     * This function takes a color in HSL format and converts it to hexadecimal.
     *
     * @param array $hsl Array containing H, S, and L values
     * @return string Color in hexadecimal format
     */
    private static function hsl_to_hex($hsl) {
        list($h, $s, $l) = $hsl;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hue_to_rgb($p, $q, $h + 1/3);
            $g = self::hue_to_rgb($p, $q, $h);
            $b = self::hue_to_rgb($p, $q, $h - 1/3);
        }

        return sprintf("#%02x%02x%02x", round($r * 255), round($g * 255), round($b * 255));
    }

    /**
     * Convert hue to RGB
     *
     * This is a helper function for hsl_to_hex, converting hue to RGB values.
     *
     * @param float $p
     * @param float $q
     * @param float $t
     * @return float
     */
    private static function hue_to_rgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }
}
