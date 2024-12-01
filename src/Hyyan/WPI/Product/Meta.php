<?php

namespace Hyyan\WPI;

use WC_Product;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product_Data_Store_CPT;
use WC_Data_Store;
use PLL_Language;
use PLL_Cache;
use PLL_MO;
use ReflectionMethod;
use WP_Locale;

final class Utilities
{
    public static function getProductTranslationsArrayByID(int $ID, bool $excludeDefault = false): array
    {
        $IDS = PLL()->model->post->get_translations($ID);
        if ($excludeDefault) {
            unset($IDS[pll_default_language()]);
        }
        return $IDS;
    }

    public static function getProductTranslationsArrayByObject(WC_Product $product, bool $excludeDefault = false): array
    {
        return static::getProductTranslationsArrayByID($product->get_id(), $excludeDefault);
    }

    public static function getProductTranslationByID(int $ID, string $slug = ''): ?WC_Product
    {
        $product = wc_get_product($ID);
        return $product ? static::getProductTranslationByObject($product, $slug) : null;
    }

    public static function getProductTranslationByObject(WC_Product $product, string $slug = ''): WC_Product
    {
        $productTranslationID = pll_get_post($product->get_id(), $slug);
        if ($productTranslationID) {
            $translated = wc_get_product($productTranslationID);
            $product = $translated ?: $product;
        }
        return $product;
    }

    public static function getLanguageEntity(string $slug): ?PLL_Language
    {
        global $polylang;
        foreach ($polylang->model->get_languages_list() as $lang) {
            if ($lang->slug === $slug) {
                return $lang;
            }
        }
        return null;
    }

    public static function getCurrentUrl(): string
    {
        return sprintf(
            '%s://%s%s',
            is_ssl() ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']
        );
    }

    public static function jsScriptWrapper(
        string $ID,
        string $code,
        bool $jquery = true,
        bool $return = false
    ): ?string {
        $prefix = 'hyyan-wpi-';
        $header = sprintf('<script type="text/javascript" id="%s">', $prefix . $ID);
        $footer = '</script>';

        $result = $jquery ? 
            sprintf("%s\n jQuery(function ($) {\n %s \n});\n %s \n", $header, $code, $footer) :
            sprintf("%s\n %s \n%s", $header, $code, $footer);

        if (!$return) {
            echo $result;
            return null;
        }
        return $result;
    }

    public static function woocommerceVersionCheck(string $version): bool
    {
        global $woocommerce;
        return version_compare($woocommerce->version, $version, '>=');
    }

    public static function polylangVersionCheck(string $version): bool
    {
        return defined('POLYLANG_VERSION') && 
               version_compare(POLYLANG_VERSION, $version, '>=');
    }

    // ... [pārējās metodes ar līdzīgām izmaiņām]
}
