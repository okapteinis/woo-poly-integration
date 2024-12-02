<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Admin\MetasList;
use Hyyan\WPI\Taxonomies\Attributes;
use WC_Product;
use PLL_Language;
use WP_Post;
use WP_Term;

class Meta
{
    public function __construct()
    {
        add_action('current_screen', [$this, 'handleProductScreen']);
        add_action('woocommerce_product_quick_edit_save', [$this, 'saveQuickEdit'], 10, 1);
        add_action('save_post_product', [$this, 'handleNewProduct'], 5, 3);
        add_filter('wc_product_has_unique_sku', [$this, 'suppressInvalidDuplicatedSKUErrorMsg'], 100, 3);

        if ('on' === Settings::getOption('importsync', Features::getID(), 'on')) {
            add_action('woocommerce_product_import_inserted_product_object', [$this, 'onImport'], 10, 2);
        }

        if ('on' === Settings::getOption('attributes', Features::getID(), 'on')) {
            add_action('woocommerce_attribute_added', [$this, 'newProductAttribute'], 10, 2);
        }
    }

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

    public static function getProductTranslationByObject(WC_Product $product, string $slug = ''): ?WC_Product
    {
        $productTranslationID = pll_get_post($product->get_id(), $slug);
        if ($productTranslationID) {
            $translated = wc_get_product($productTranslationID);
            return $translated ?: $product;
        }
        return $product;
    }

    public function handleNewProduct(int $post_id, WP_Post $post, bool $update): void
    {
        if (!$update && isset($_GET['from_post'])) {
            $this->syncTaxonomiesAndProductAttributes($post_id, $post, $update);
        }
    }

    public function newProductAttribute(int $insert_id, array $attribute): void
    {
        $options = get_option('polylang');
        $sync = $options['taxonomies'];
        $attrname = 'pa_' . $attribute['attribute_name'];
        
        if (!in_array($attribute, $sync)) {
            $options['taxonomies'][] = $attrname;
            update_option('polylang', $options);
        }
    }

    public function onImport(WC_Product $object, array $data): void
    {
        $ProductID = $object->get_id();
        if ($ProductID) {
            do_action('pll_save_post', $ProductID, $object, PLL()->model->post->get_translations($ProductID));
            $this->syncTaxonomiesAndProductAttributes($ProductID, $object, true);
        }
    }

    
}
