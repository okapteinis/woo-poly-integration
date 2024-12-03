<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Utilities;
use WC_Product;

class Stock
{
    public function __construct()
    {
        if (!($screen = get_current_screen()) || $screen->post_type === 'product') {
            return;
        }

        add_action('woocommerce_product_set_stock', [__CLASS__, 'SyncStockSimple']);
        add_action('woocommerce_variation_set_stock', [__CLASS__, 'SyncStockVariation']);
    }

    public static function SyncStockSimple(WC_Product $product): void
    {
        remove_action('woocommerce_product_set_stock', [__CLASS__, __FUNCTION__], 10);
        static::SyncStock($product);
        add_action('woocommerce_product_set_stock', [__CLASS__, __FUNCTION__], 10);
    }

    public static function SyncStockVariation(WC_Product $product): void
    {
        remove_action('woocommerce_variation_set_stock', [__CLASS__, __FUNCTION__], 10);
        static::SyncStock($product);
        add_action('woocommerce_variation_set_stock', [__CLASS__, __FUNCTION__], 10);
    }

    public static function SyncStock(WC_Product $product): void
    {
        $product_id_with_stock = $product->get_stock_managed_by_id();
        $product_with_stock = $product_id_with_stock !== $product->get_id() ?
            wc_get_product($product_id_with_stock) :
            $product;

        if (!$product_with_stock || !$product_with_stock->get_id()) {
            return;
        }

        $targetValue = $product_with_stock->get_stock_quantity();
        $product_translations = self::getProductTranslations($product_with_stock);
        
        if (!$product_translations) {
            return;
        }

        $target_status = $product_with_stock->get_stock_status();
        self::updateTranslationsStock(
            $product_translations,
            $product_with_stock,
            $targetValue,
            $target_status
        );
    }

    private static function getProductTranslations(WC_Product $product): array
    {
        if ($product->is_type('variation')) {
            return self::getVariationTranslations($product);
        }
        return Utilities::getProductTranslationsArrayByObject($product);
    }

    private static function getVariationTranslations(WC_Product $product): array
    {
        $base_variation_id = Utilities::get_translated_variation(
            $product->get_id(),
            pll_default_language()
        );
        $translations = Variation::getRelatedVariation(
            get_post_meta($base_variation_id, Variation::DUPLICATE_KEY, true),
            true
        );
        return self::adjustVariationTranslations(
            $translations,
            $product->get_stock_managed_by_id(),
            $base_variation_id
        );
    }

    private static function adjustVariationTranslations(
        array $translations,
        int $product_id_with_stock,
        int $base_variation_id
    ): array {
        if ($base_variation_id !== $product_id_with_stock) {
            $translations = array_filter(
                $translations,
                fn($id) => $id !== $product_id_with_stock
            );
            if (!in_array($base_variation_id, $translations, true)) {
                $translations[] = $base_variation_id;
            }
        }
        return $translations;
    }

    private static function updateTranslationsStock(
        array $translations,
        WC_Product $product_with_stock,
        ?float $targetValue,
        string $target_status
    ): void {
        foreach ($translations as $translation_id) {
            if ($translation_id === $product_with_stock->get_id()) {
                continue;
            }

            $translation = wc_get_product($translation_id);
            if (!$translation) {
                continue;
            }

            wc_update_product_stock($translation, $targetValue, 'set', true);
            wc_update_product_stock_status($translation_id, $target_status);
            if ($parent_id = $translation->get_parent_id()) {
                wc_update_product_stock_status($parent_id, $target_status);
            }
        }
    }
}
