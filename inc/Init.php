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
            Api\Controllers\AppointmentController::class,
            Api\Controllers\AppointmentReportingController::class,
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
    private static function instantiate($class)
    {
        switch ($class) 
        {
            case 'Inc\\Api\\Controllers\\ServiceController':
                $serviceRepository = new \Inc\Api\Repositories\ServiceRepository();
                $serviceService = new \Inc\Api\Services\ServiceService($serviceRepository);
                return new $class($serviceService);

            case 'Inc\\Api\\Controllers\\AppointmentController':
                $appointmentRepository = new \Inc\Api\Repositories\AppointmentRepository();
                $emailSender = new \Inc\EmailConfirmation\EmailSender();
                $appointmentService = new \Inc\Api\Services\AppointmentService($appointmentRepository, $emailSender);
                return new $class($appointmentService);

            case 'Inc\\Api\\Controllers\\AppointmentReportingController':
                $appointmentRepository = new \Inc\Api\Repositories\AppointmentRepository();
                $timeSlotGenerator = new \Inc\Api\Callbacks\TimeSlotGenerator();
                $reportingService = new \Inc\Api\Services\AppointmentReportingService($appointmentRepository, $timeSlotGenerator);
                return new $class($reportingService);

            default:
                return new $class();
        }
    }
}