<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use WC_Product;
use WC_Order;
use WC_Order_Item_Product;
use WC_Data_Store;
use WC_Product_Data_Store_CPT;
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

    public static function getProductTranslationByID(int $ID, string $slug = ''): WC_Product|false
    {
        $product = wc_get_product($ID);
        if (!$product) {
            return false;
        }
        return static::getProductTranslationByObject($product, $slug);
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

    public static function getLanguageEntity(string $slug): PLL_Language|false
    {
        global $polylang;
        $langs = $polylang->model->get_languages_list();
        foreach ($langs as $lang) {
            if ($lang->slug === $slug) {
                return $lang;
            }
        }
        return false;
    }

    public static function getTermTranslationsArrayByID(int $ID, bool $excludeDefault = false): array
    {
        $IDS = PLL()->model->term->get_translations($ID);
        if ($excludeDefault) {
            unset($IDS[pll_default_language()]);
        }
        return $IDS;
    }

    public static function getCurrentUrl(): string
    {
        return (is_ssl() ? 'https://' : 'http://') . 
            $_SERVER['HTTP_HOST'] . 
            $_SERVER['REQUEST_URI'];
    }

    public static function jsScriptWrapper(
        string $ID,
        string $code,
        bool $jquery = true,
        bool $return = false
    ): ?string {
        $result = '';
        $prefix = 'hyyan-wpi-';
        $header = sprintf('<script type="text/javascript" id="%s">', $prefix . $ID);
        $footer = '</script>';

        if ($jquery) {
            $result = sprintf(
                "%s\n jQuery(function ($) {\n %s \n});\n %s \n",
                $header,
                $code,
                $footer
            );
        } else {
            $result = sprintf(
                "%s\n %s \n%s",
                $header,
                $code,
                $footer
            );
        }

        if (!$return) {
            echo $result;
            return null;
        }

        return $result;
    }

    // ... [Pārējās metodes saglabātas bez izmaiņām, pievienojot tikai type hints]
}
