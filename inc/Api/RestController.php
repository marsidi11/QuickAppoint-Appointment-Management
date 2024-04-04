<?php
/**
 * @package BookingManagementPlugin
 */
namespace Inc\Api;

/**
 * Base REST API controller class (abstract class)
 */
abstract class RestController 
{
    /**
     * Namespace for the REST API routes
     *
     * @var string
     */
    protected $namespace;

    /**
     * Base URL for the REST API routes
     *
     * @var string
     */
    protected $base;

    /**
     * Register the REST API routes
     */
    abstract public function register_routes();

    /**
     * Get the namespace for the REST API routes
     *
     * @return string
     */
    abstract protected function get_namespace();

    /**
     * Get the base URL for the REST API routes
     *
     * @return string
     */
    abstract protected function get_base();
}