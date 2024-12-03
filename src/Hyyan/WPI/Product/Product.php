<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;

class Product
{
    public function __construct()
    {
        $this->initializeComponents();
    }

    private function initializeComponents(): void
    {
        new Meta();
        new Variable();
        new Duplicator();

        if ('on' === Settings::getOption('stock', Features::getID(), 'on') &&
            'yes' === get_option('woocommerce_manage_stock')
        ) {
            new Stock();
        }
    }

    public static function sync_ajax_woocommerce_feature_product(): void
    {
        $metas = Meta::getDisabledProductMetaToCopy();
        if (in_array('_visibility', $metas, true)) {
            return;
        }

        $product = wc_get_product(absint($_GET['product_id'] ?? 0));
        if (!$product) {
            return;
        }

        $targetValue = !$product->get_featured();
        $product_translations = Utilities::getProductTranslationsArrayByObject($product);
        foreach ($product_translations as $product_translation) {
            if ($product_translation !== $product->get_id()) {
                $translation = wc_get_product($product_translation);
                if ($translation) {
                    $translation->set_featured($targetValue);
                    $translation->save();
                }
            }
        }
    }

    public function getChildrenInLanguage(array $relatedIds, \WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getUpsellsInLanguage(array $relatedIds, \WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getCrosssellsInLanguage(array $relatedIds, \WC_Product $product): array
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }

    public function getProductIdsInLanguage(array $productIds, \WC_Product $product): array
    {
        $productLang = pll_get_post_language($product->get_id());
        $mappedIds = [];
        foreach ($productIds as $productId) {
            $correctLanguageId = pll_get_post($productId, $productLang);
            $mappedIds[] = $correctLanguageId ?: $productId;
        }
        return $mappedIds;
    }

    public function manageProductTranslation(array $types): array
    {
        $options = get_option('polylang');
        $postTypes = $options['post_types'] ?? [];
        foreach (['product', 'product_variation'] as $type) {
            if (!in_array($type, $postTypes, true)) {
                $options['post_types'][] = $type;
                update_option('polylang', $options);
            }
        }
        $types[] = 'product';
        return $types;
    }

    public function syncPostParent(): void
    {
        $options = get_option('polylang');
        $sync = $options['sync'] ?? [];
        if (!in_array('post_parent', $sync, true)) {
            $options['sync'][] = 'post_parent';
            update_option('polylang', $options);
        }
    }

    public function wpi_editor_title(string $title): string
    {
        return $this->getPostField('post_title', $title);
    }

    public function wpi_editor_content(string $content): string
    {
        return $this->getPostField('post_content', $content);
    }

    public function wpi_editor_excerpt(string $excerpt): string
    {
        return $this->getPostField('post_excerpt', $excerpt);
    }

    private function getPostField(string $field, string $default): string
    {
        if (!isset($_GET['from_post'])) {
            return $default;
        }
        $post = get_post($_GET['from_post']);
        return $post ? $post->$field : $default;
    }
}
