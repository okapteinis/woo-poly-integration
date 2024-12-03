<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;
use WC_Order;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

class Shipping
{
    public function __construct()
    {
        add_action('wp_loaded', [$this, 'registerShippingStringsForTranslation']);
        add_filter('woocommerce_shipping_rate_label', [$this, 'translateShippingLabel'], 10, 1);
        add_filter('woocommerce_order_shipping_method', [$this, 'translateOrderShippingMethod'], 10, 2);
    }

    public function disableSettings(): bool
    {
        $currentScreen = function_exists('get_current_screen') ? get_current_screen() : false;
        if ($currentScreen && $currentScreen->id !== 'settings_page_hyyan-wpi') {
            return false;
        }

        add_action('admin_print_scripts', [$this, 'disableShippingClassFeature'], 100);
        return true;
    }

    public function disableShippingClassFeature(): void
    {
        $jsID = 'shipping-class-translation-disabled';
        $code = '$( "#wpuf-wpi-features\\\[shipping-class\\\]" ).prop( "disabled", true );';
        Utilities::jsScriptWrapper($jsID, $code);
    }

    private function getActiveShippingMethods(): array
    {
        $active_methods = [];
        $shipping_methods = $this->getZonesShippingMethods();

        foreach ($shipping_methods as $id => $shipping_method) {
            if (isset($shipping_method->enabled) && 'yes' === $shipping_method->enabled) {
                $active_methods[$id] = $shipping_method->plugin_id;
            }
        }

        return $active_methods;
    }

    public function getZonesShippingMethods(): array
    {
        $zones = [];
        $shipping_methods = [];

        $zone = new WC_Shipping_Zone();
        $zones[$zone->get_id()] = [
            ...$zone->get_data(),
            'formatted_zone_location' => $zone->get_formatted_location(),
            'shipping_methods' => $zone->get_shipping_methods()
        ];

        $zones = array_merge($zones, WC_Shipping_Zones::get_zones());

        foreach ($zones as $zone) {
            foreach ($zone['shipping_methods'] as $instance_id => $shipping_method) {
                $shipping_methods[$shipping_method->id . '_' . $instance_id] = $shipping_method;
            }
        }

        return $shipping_methods;
    }

    public function registerShippingStringsForTranslation(): void
    {
        if (!function_exists('pll_register_string')) {
            return;
        }

        $shipping_methods = $this->getActiveShippingMethods();
        foreach ($shipping_methods as $method_id => $plugin_id) {
            $setting = get_option($plugin_id . $method_id . '_settings');
            if ($setting && isset($setting['title'])) {
                pll_register_string(
                    $plugin_id . $method_id . '_shipping_method',
                    $setting['title'],
                    __('WooCommerce Shipping Methods', 'woo-poly-integration')
                );
            }
        }
    }

    public function translateShippingLabel(string $label): string
    {
        return function_exists('pll__') ? pll__($label) : __($label, 'woocommerce');
    }

    public function translateOrderShippingMethod(string $implode, WC_Order $instance): string
    {
        $shipping_methods = explode(', ', $implode);
        $translated = [];

        foreach ($shipping_methods as $shipping) {
            if (function_exists('pll__')) {
                $translated[] = pll__($shipping);
            } else {
                $translated[] = __($shipping, 'woocommerce');
            }
        }

        return implode(', ', $translated);
    }
}
