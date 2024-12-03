<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

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

        if (is_admin()) {
            if (defined('DOING_AJAX') || (function_exists('is_ajax') && is_ajax())) {
                //skipping ajax
            } else {
                $wcpagecheck_passed = get_option('wpi_wcpagecheck_passed');
                $check_pages = Settings::getOption('checkpages', Features::getID(), 0);
                if (($check_pages && $check_pages != 'off') || !($wcpagecheck_passed)) {
                    add_action('current_screen', [__CLASS__, 'wpi_ensure_woocommerce_pages_translated']);
                }
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

    protected function registerCore(): void
    {
        new Emails();
        new Admin\Settings();
        new Cart();
        new Order();
        new Pages();
        new Endpoints();
        new Product\Product();
        new Taxonomies\Taxonomies();
        new Media();
        new Permalinks();
        new Privacy();
        new Language();
        new Coupon();
        new Reports();
        new Widgets\SearchWidget();
        new Widgets\LayeredNav();
        new Gateways();
        new Shipping();
        new Breadcrumb();
        new Tax();
        new LocaleNumbers();
        new Ajax();
    }

    public static function getVersion(): string
    {
        $data = get_plugin_data(Hyyan_WPI_DIR);
        return $data['Version'];
    }

    public static function getView(string $name, array $vars = []): string
    {
        $result = '';
        $path = dirname(Hyyan_WPI_DIR) . '/src/Hyyan/WPI/Views/' . $name . '.php';
        if (file_exists($path)) {
            ob_start();
            include $path;
            $result = ob_get_clean();
        }
        return $result;
    }

    public static function canActivate(): bool
    {
        $polylang = false;
        $woocommerce = false;

        if (class_exists('Polylang')) {
            if (isset($GLOBALS['polylang'], \PLL()->model, PLL()->links_model)) {
                if (pll_default_language()) {
                    $polylang = true;
                }
            }
        }

        if (class_exists('WooCommerce')) {
            $woocommerce = true;
        }

        return ($polylang && Utilities::polylangVersionCheck(self::POLYLANG_VERSION)) &&
               ($woocommerce && Utilities::woocommerceVersionCheck(self::WOOCOMMERCE_VERSION));
    }
}
