<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use WC_Order;

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
        if (!$currentScreen || $currentScreen->id !== 'settings_page_hyyan-wpi') {
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
        $activeMethods = [];
        $shippingMethods = $this->getZonesShippingMethods();

        foreach ($shippingMethods as $id => $shippingMethod) {
            if (isset($shippingMethod->enabled) && $shippingMethod->enabled === 'yes') {
                $activeMethods[$id] = $shippingMethod->plugin_id;
            }
        }

        return $activeMethods;
    }

    public function getZonesShippingMethods(): array
    {
        $zones = [];
        $shippingMethods = [];

        $zone = new WC_Shipping_Zone();
        $zones[$zone->get_id()] = [
            ...$zone->get_data(),
            'formatted_zone_location' => $zone->get_formatted_location(),
            'shipping_methods' => $zone->get_shipping_methods()
        ];

        $zones = array_merge($zones, WC_Shipping_Zones::get_zones());

        foreach ($zones as $zone) {
            foreach ($zone['shipping_methods'] as $instanceId => $shippingMethod) {
                $shippingMethods[$shippingMethod->id . '_' . $instanceId] = $shippingMethod;
            }
        }

        return $shippingMethods;
    }

    public function registerShippingStringsForTranslation(): void
    {
        if (!function_exists('pll_register_string')) {
            return;
        }

        $shippingMethods = $this->getActiveShippingMethods();
        foreach ($shippingMethods as $methodId => $pluginId) {
            $setting = get_option($pluginId . $methodId . '_settings');
            if ($setting && isset($setting['title'])) {
                pll_register_string(
                    $pluginId . $methodId . '_shipping_method',
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
        $shippingMethods = explode(', ', $implode);
        $translated = array_map(
            fn($shipping) => function_exists('pll__') ? 
                pll__($shipping) : 
                __($shipping, 'woocommerce'),
            $shippingMethods
        );

        return implode(', ', $translated);
    }
}
