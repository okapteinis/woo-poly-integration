<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use WC_Product;
use PLL_Language;

class Meta
{
    public static function getProductTranslationsArrayByID(int $ID, bool $excludeDefault = false): array
    {
        global $polylang;
        $IDS = $polylang->model->post->get_translations($ID);

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
        $header = sprintf('/* %s */', $prefix . $ID);
        // Implementation continues...
    }
}
