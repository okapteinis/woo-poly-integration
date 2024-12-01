<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Utilities;
use WC_Product;

class Tax
{
    public function __construct()
    {
        add_filter('woocommerce_get_price_suffix', [$this, 'filterPriceSuffix'], 10, 4);
        $this->registerTaxStringsForTranslation();
    }

    public function registerTaxStringsForTranslation(): void
    {
        if (wc_tax_enabled() && 'taxable') {
            $suffix = get_option('woocommerce_price_display_suffix');
            if ($suffix) {
                $this->registerTaxString('woocommerce_price_display_suffix', $suffix);
            }
        }
    }

    public function registerTaxString(string $setting, string $default = ''): void
    {
        if (!function_exists('pll_register_string')) {
            return;
        }

        $value = get_option($setting) ?: $default;
        if ($value) {
            pll_register_string(
                $setting,
                $value,
                __('Woocommerce Taxes', 'woo-poly-integration')
            );
        }
    }

    public function translateTaxString(string $tax_string): string
    {
        if (!function_exists('pll_register_string')) {
            return $tax_string;
        }

        return pll__($tax_string) ?: $tax_string;
    }

    public function filterPriceSuffix(
        string $html,
        WC_Product $instance,
        string $price,
        int $qty
    ): string {
        if (!wc_tax_enabled() || 
            $instance->get_tax_status() !== 'taxable' || 
            !($suffix = get_option('woocommerce_price_display_suffix'))
        ) {
            return '';
        }

        $suffix = $this->translateTaxString($suffix);
        $price = $price ?: $instance->get_price();

        $replacements = [
            '{price_including_tax}' => wc_price(
                wc_get_price_including_tax($instance, ['qty' => $qty, 'price' => $price])
            ),
            '{price_excluding_tax}' => wc_price(
                wc_get_price_excluding_tax($instance, ['qty' => $qty, 'price' => $price])
            ),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            sprintf(
                ' <small class="woocommerce-price-suffix">%s</small>',
                wp_kses_post($suffix)
            )
        );
    }
}
