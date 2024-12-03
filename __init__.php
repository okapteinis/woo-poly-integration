<?php

declare(strict_types=1);

/*
 * Plugin Name: Hyyan WooCommerce Polylang Integration
 * Plugin URI: https://github.com/hyyan/woo-poly-integration/
 * Description: Integrates Woocommerce with Polylang
 * Author: Hyyan Abo Fakher
 * Author URI: https://github.com/hyyan
 * Text Domain: woo-poly-integration
 * Domain Path: /languages
 * GitHub Plugin URI: hyyan/woo-poly-integration
 * License: MIT License
 * Version: 1.6.0
 * Requires At Least: 6.0
 * Tested Up To: 6.4
 * WC requires at least: 3.0.0
 * WC tested up to: 8.5.0
 * Requires PHP: 8.3
 */

if (!defined('ABSPATH')) {
    exit('restricted access');
}

define('Hyyan_WPI_DIR', __FILE__);
define('Hyyan_WPI_URL', plugin_dir_url(__FILE__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once __DIR__ . '/vendor/class.settings-api.php';
require_once __DIR__ . '/src/Hyyan/WPI/Autoloader.php';

/* register the autoloader */
new Hyyan\WPI\Autoloader(__DIR__ . '/src/');

/* bootstrap the plugin */
new Hyyan\WPI\Plugin();

function onActivate(): void
{
    update_option('wpi_wcpagecheck_passed', false);
    update_option('hyyan-wpi-flash-messages', '');
}

register_activation_hook(__FILE__, 'onActivate');
