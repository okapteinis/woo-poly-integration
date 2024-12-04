<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;
use WC_Product;

class Product
{
    public function __construct()
    {
        add_filter('pll_get_post_types', [$this, 'manageProductTranslation']);
        add_filter('admin_init', [$this, 'syncPostParent']);

        $translate_option = Settings::getOption('new-translation-defaults', Features::getID(), 0);
        if ($translate_option) {
            add_filter('default_title', [$this, 'wpi_editor_title']);
            add_filter('default_content', [$this, 'wpi_editor_content']);
            add_filter('default_excerpt', [$this, 'wpi_editor_excerpt']);

            add_filter('woocommerce_product_get_upsell_ids', [$this, 'getUpsellsInLanguage'], 10, 2);
            add_filter('woocommerce_product_get_cross_sell_ids', [$this, 'getCrosssellsInLanguage'], 10, 2);
            add_filter('woocommerce_product_get_children', [$this, 'getChildrenInLanguage'], 10, 2);
        }

        add_action('wp_ajax_woocommerce_feature_product', [__CLASS__, 'sync_ajax_woocommerce_feature_product'], 5);

        new Meta();
        new Variable();
        new Duplicator();

        if ('on' === Settings::getOption('stock', Features::getID(), 'on') &&
            'yes' === get_option('woocommerce_manage_stock')) {
            new Stock();
        }
    }

    public static function sync_ajax_woocommerce_feature_product(): void
    {
        $metas = Meta::getDisabledProductMetaToCopy();
        if (in_array('_visibility', $metas, true)) {
            return;
        }

        $product = wc_get_product(absint($_GET['product_id']));
        if (!$product) {
            return;
        }

        $targetValue = !$product->get_featured();
        $product_translations = Utilities::getProductTranslationsArrayByObject($product);

        foreach ($product_translations as $product_translation) {
            if ($product_translation == $product->get_id()) {
                continue;
            }

            $translation = wc_get_product($product_translation);
            if ($translation) {
                $translation->set_featured($targetValue);
                $translation->save();
            }
        }
    }

    public function getChildrenInLanguage(array $relatedIds, WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getUpsellsInLanguage(array $relatedIds, WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getCrosssellsInLanguage(array $relatedIds, WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getProductIdsInLanguage(array $productIds, WC_Product $product): array
    {
        $productLang = pll_get_post_language($product->get_id());
        $mappedIds = [];

        foreach ($productIds as $productId) {
            $correctLanguageId = pll_get_post($productId, $productLang);
            if ($correctLanguageId) {
                $mappedIds[] = $correctLanguageId;
            } else {
                $mappedIds[] = $productId;
            }
        }

        return $mappedIds;
    }

    public function manageProductTranslation(array $types): array
    {
        $options = get_option('polylang');
        $postTypes = $options['post_types'];

        if (!in_array('product', $postTypes, true)) {
            $options['post_types'][] = 'product';
            update_option('polylang', $options);
        }

        if (!in_array('product_variation', $postTypes, true)) {
            $options['post_types'][] = 'product_variation';
            update_option('polylang', $options);
        }

        $types[] = 'product';
        return $types;
    }

    public function syncPostParent(): void
    {
        $options = get_option('polylang');
        $sync = $options['sync'];

        if (!in_array('post_parent', $sync, true)) {
            $options['sync'][] = 'post_parent';
            update_option('polylang', $options);
        }
    }

    public function wpi_editor_title(string $title): string
    {
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_title;
            }
        }
        return $title;
    }

    public function wpi_editor_content(string $content): string
    {
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_content;
            }
        }
        return $content;
    }

    public function wpi_editor_excerpt(string $excerpt): string
    {
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_excerpt;
            }
        }
        return $excerpt;
    }
}
