<?php
/**
 * @package AppointmentManagementPlugin
 */

namespace Inc;

/**
 * Stores all the classes inside an array and provides methods to loop through the classes, initialize them, and call the register() method if it exists.
 */
final class Init {

    /**
     * Store all the classes inside an array
     * @return array All classes
     */
    public static function get_services() {
        return [
            Pages\Admin::class,
            Base\Enqueue::class,
            Base\SettingsLinks::class,
            Pages\Shortcodes::class,
            Api\AppointmentsDataController::class,
            Api\Controllers\ServiceController::class,
            Api\CustomOptionsDataController::class,
            EmailConfirmation\ConfirmationHandler::class,
        ];
    }

    /**
     * Loop through the classes, initialize them, and call the register() method if it exists
     * @return void
     */
    public static function register_services() {
        foreach (self::get_services() as $class) {
            if (class_exists($class)) {
                $instance = self::instantiate($class);
                if (method_exists($instance, 'register')) {
                    $instance->register();
                }
            }
        }
    }

    /**
     * Initialize the class, handling special cases where dependencies need to be injected.
     * @param string $class Class name from the services array.
     * @return object Instance of the class.
     */
    private static function instantiate($class) {
        switch ($class) {
            case 'Inc\\Api\\Controllers\\ServiceController':
                $serviceRepository = new \Inc\Api\Repositories\ServiceRepository();
                $serviceService = new \Inc\Api\Services\ServiceService($serviceRepository);
                return new $class($serviceService);
            // Add cases for other classes with dependencies if needed
            default:
                return new $class();
        }
    }
}