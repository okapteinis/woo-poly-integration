<?php
/**
 * PHPUnit bootstrap file for WooCommerce Polylang Integration
 *
 * @package Hyyan\WPI
 */

// Load Composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Load PHPUnit Polyfills
if (defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    require_once WP_TESTS_PHPUNIT_POLYFILLS_PATH . '/phpunitpolyfills-autoload.php';
}

// Define test constants
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__DIR__) . '/tests/wordpress/wp-content');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/tests/wordpress/');
}

// Mock WordPress functions for testing if WordPress isn't loaded
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Mock implementation for testing
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Mock implementation for testing
        return true;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) {
        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        // Mock implementation for testing
    }
}

// Load the plugin autoloader if it exists
if (file_exists(dirname(__DIR__) . '/src/Hyyan/WPI/Autoloader.php')) {
    require_once dirname(__DIR__) . '/src/Hyyan/WPI/Autoloader.php';
}

// PHPUnit 10+ enforces strict output rules during tests - commenting out echo
// echo "PHPUnit Bootstrap loaded successfully\n";
