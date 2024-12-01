<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use NumberFormatter;

class LocaleNumbers
{
    public function __construct()
    {
        if (class_exists(NumberFormatter::class) &&
            'on' === Settings::getOption('localenumbers', Features::getID(), 'on')
        ) {
            add_filter('wc_get_price_decimal_separator', [$this, 'getLocaleDecimalSeparator'], 10, 1);
            add_filter('wc_get_price_thousand_separator', [$this, 'getLocaleThousandSeparator'], 10, 1);
            add_filter('wc_price_args', [$this, 'filterPriceArgs'], 10, 1);
            add_filter('woocommerce_format_localized_decimal', [$this, 'getLocalizedDecimal'], 10, 2);
        }
    }

    public function filterPriceArgs(array $args): array
    {
        if (!empty($args['currency'])) {
            $formatter = new NumberFormatter(
                pll_current_language('locale') . '@currency=' . $args['currency'],
                NumberFormatter::CURRENCY
            );
            
            $args['decimal_separator'] = $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            $args['thousand_separator'] = $formatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
            $args['decimals'] = $formatter->getAttribute(NumberFormatter::FRACTION_DIGITS);
            $args['price_format'] = $formatter->getTextAttribute(NumberFormatter::POSITIVE_PREFIX) ? '%1$s%2$s' : '%2$s%1$s';
        } else {
            $args['decimal_separator'] = $this->getLocaleDecimalSeparator($args['decimal_separator']);
            $args['thousand_separator'] = $this->getLocaleThousandSeparator($args['decimal_separator']);
        }
        
        return $args;
    }

    public function getLocalizedDecimal(string $wooFormattedValue, float $input): string
    {
        if (is_admin() && !isset($_REQUEST['get_product_price_by_ajax'])) {
            return $wooFormattedValue;
        }

        $formatter = new NumberFormatter(pll_current_language('locale'), NumberFormatter::DECIMAL);
        return $formatter ? $formatter->format($input, NumberFormatter::TYPE_DOUBLE) : $wooFormattedValue;
    }

    public function getLocaleDecimalSeparator(string $separator): string
    {
        if (is_admin() && !isset($_REQUEST['get_product_price_by_ajax'])) {
            return $separator;
        }

        $formatter = new NumberFormatter(pll_current_language('locale'), NumberFormatter::DECIMAL);
        if ($formatter) {
            $localeResult = $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            return $localeResult ?: $separator;
        }
        
        return $separator;
    }

    public function getLocaleThousandSeparator(string $separator): string
    {
        if (is_admin()) {
            return $separator;
        }

        $formatter = new NumberFormatter(pll_current_language('locale'), NumberFormatter::DECIMAL);
        return $formatter ? 
            $formatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL) : 
            $separator;
    }
}
