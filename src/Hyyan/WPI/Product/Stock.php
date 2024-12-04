<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Variation;
use WC_Product;

class Stock
{
    public function __construct()
    {
        if (($screen = get_current_screen()) && $screen->post_type === 'product' || isset($_POST['product-type'])) {
            return;
        }

        add_action(
            'woocommerce_product_set_stock',
            [__CLASS__, 'SyncStockSimple']
        );
        add_action(
            'woocommerce_variation_set_stock',
            [__CLASS__, 'SyncStockVariation']
        );
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
        $product_with_stock = $product_id_with_stock !== $product->get_id() 
            ? wc_get_product($product_id_with_stock) 
            : $product;

        if (!$product_with_stock || !$product_with_stock->get_id()) {
            return;
        }

        $targetValue = $product_with_stock->get_stock_quantity();
        $product_translations = [];

        if ($product_with_stock->is_type('variation')) {
            $base_variation_id = Utilities::get_translated_variation(
                $product_with_stock->get_id(),
                pll_default_language()
            );
            $product_translations = Variation::getRelatedVariation(
                get_post_meta($base_variation_id, Variation::DUPLICATE_KEY, true),
                true
            );

            if ($base_variation_id != $product_id_with_stock) {
                if (($key = array_search($product_id_with_stock, $product_translations)) !== false) {
                    unset($product_translations[$key]);
                }
                $key = array_search($base_variation_id, $product_translations);
                if ($key === false) {
                    $product_translations[] = $base_variation_id;
                }
            }
        } else {
            $product_translations = Utilities::getProductTranslationsArrayByObject($product_with_stock);
        }

        if (!$product_translations) {
            return;
        }

        $target_status = $product_with_stock->get_stock_status();
        foreach ($product_translations as $product_translation) {
            if ($product_translation == $product_with_stock->get_id()) {
                continue;
            }

            $translation = wc_get_product($product_translation);
            if (!$translation) {
                continue;
            }

            wc_update_product_stock($translation, $targetValue, 'set', true);
            wc_update_product_stock_status($product_translation, $target_status);

            if ($translation->get_parent_id()) {
                wc_update_product_stock_status($translation->get_parent_id(), $target_status);
            }
        }
    }
}
