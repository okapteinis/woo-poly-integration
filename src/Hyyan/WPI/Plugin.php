<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use WP;

class Plugin
{
    public const WOOCOMMERCE_VERSION = '3.0.0';
    public const POLYLANG_VERSION = '2.0.0';

    public function __construct()
    {
        FlashMessages::register();

        add_action('init', [$this, 'activate']);
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('admin_init', [__CLASS__, 'admin_activate']);
        add_action('pll_add_language', [__CLASS__, 'handleNewLanguage']);

        if (is_admin() && 
            (!defined('DOING_AJAX') && (!function_exists('is_ajax') || !is_ajax()))
        ) {
            $wcpagecheck_passed = get_option('wpi_wcpagecheck_passed');
            $check_pages = Settings::getOption('checkpages', Features::getID(), 0);
            
            if ($check_pages && $check_pages !== 'off' || !$wcpagecheck_passed) {
                add_action('current_screen', [__CLASS__, 'wpi_ensure_woocommerce_pages_translated']);
            }
        }
    }

    public static function admin_activate(): void
    {
        include_once(plugin_dir_path(__FILE__) . 'Admin/StatusReport.php');
    }

    public static function handleNewLanguage(array $args): void
    {
        update_option('wpi_wcpagecheck_passed', false);
    }

    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'woo-poly-integration',
            false,
            plugin_basename(dirname(Hyyan_WPI_DIR)) . '/languages'
        );
    }

    public function activate(): bool
    {
        if (!static::canActivate()) {
            FlashMessages::remove(MessagesInterface::MSG_SUPPORT);
            FlashMessages::add(
                MessagesInterface::MSG_ACTIVATE_ERROR,
                static::getView('Messages/activateError'),
                ['error'],
                true
            );
            return false;
        }

        FlashMessages::remove(MessagesInterface::MSG_ACTIVATE_ERROR);
        FlashMessages::add(
            MessagesInterface::MSG_SUPPORT,
            static::getView('Messages/support')
        );

        $this->addPluginLinks();
        $this->handleUpgrade();
        $this->registerCore();

        return true;
    }

    private function addPluginLinks(): void
    {
        add_filter(
            'plugin_action_links_woo-poly-integration/__init__.php',
            function(array $links): array {
                return [
                    static::settingsLinkHTML(),
                    '<a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki">' .
                    __('Docs', 'woo-poly-integration') .
                    '</a>'
                ] + $links;
            }
        );
        
        add_filter('plugin_row_meta', [__CLASS__, 'plugin_row_meta'], 10, 2);
    }

    private function handleUpgrade(): void
    {
        $oldVersion = get_option('wpi_version');
        $wcpagecheck_passed = get_option('wpi_wcpagecheck_passed');
        
        if (!$wcpagecheck_passed || version_compare(self::getVersion(), $oldVersion, '<>')) {
            self::onUpgrade(self::getVersion(), $oldVersion);
        }
    }

    public static function settingsLinkHTML(): string
    {
        $baseURL = is_multisite() ? get_admin_url() : admin_url();
        return sprintf(
            '<a href="%soptions-general.php?page=hyyan-wpi">%s</a>',
            $baseURL,
            __('Settings', ' woo-poly-integration')
        );
    }

    // ... [pārējās metodes ar līdzīgām izmaiņām]
}
