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

    public static function getProductMetaToCopy(array $metas = [], bool $flat = true): array
    {
        $default = apply_filters(HooksInterface::PRODUCT_META_SYNC_FILTER, [
            'general' => [
                'name' => __('General Metas', 'woo-poly-integration'),
                'desc' => __('General Metas', 'woo-poly-integration'),
                'metas' => [
                    'product-type',
                    '_virtual',
                    '_sku',
                    '_upsell_ids',
                    '_crosssell_ids',
                    '_children',
                    '_product_image_gallery',
                    'total_sales',
                    '_translation_porduct_type',
                    '_visibility',
                ],
            ],
            'polylang' => [
                'name' => __('Polylang Metas', 'woo-poly-integration'),
                'desc' => __('To control these values please check ', 'woo-poly-integration') .
                    ' ' . __('Polylang admin menu "Languages, Settings"') . ' ' .
                    __('Synchronisation section values for Page order, Featured image, Comment Status', 'woo-poly-integration'),
                'metas' => [
                    'menu_order',
                    '_thumbnail_id',
                    'comment_status',
                ],
            ],
            'stock' => [
                'name' => __('Stock Metas', 'woo-poly-integration'),
                'desc' => __('Stock Metas: see also Features, Stock Sync', 'woo-poly-integration'),
                'metas' => [
                    '_manage_stock',
                    '_stock',
                    '_backorders',
                    '_stock_status',
                    '_low_stock_amount',
                    '_sold_individually',
                ],
            ],
            'shipping' => [
                'name' => __('ShippingClass Metas', 'woo-poly-integration'),
                'desc' => __('Shipping size and weight metas and Shipping class taxonomy', 'woo-poly-integration'),
                'metas' => [
                    '_weight',
                    '_length',
                    '_width',
                    '_height',
                    'product_shipping_class',
                ],
            ],
            'Attributes' => [
                'name' => __('Attributes Metas', 'woo-poly-integration'),
                'desc' => __('To select individual Product Attributes for translation or synchronization, turn on here and check', 'woo-poly-integration') .
                    ' ' . __('Polylang admin menu "Languages, Settings"') . ' ' .
                    __(' "Custom post types and Taxonomies", "Custom Taxonomies"', 'woo-poly-integration'),
                'metas' => [
                    '_product_attributes',
                    '_custom_product_attributes',
                    '_default_attributes',
                ],
            ],
            'Downloadable' => [
                'name' => __('Downloadable Metas', 'woo-poly-integration'),
                'desc' => __('Downloadable product Meta', 'woo-poly-integration'),
                'metas' => [
                    '_downloadable',
                    '_downloadable_files',
                    '_download_limit',
                    '_download_expiry',
                    '_download_type',
                ],
            ],
            'Taxes' => [
                'name' => __('Taxes Metas', 'woo-poly-integration'),
                'desc' => __('Taxes Metas', 'woo-poly-integration'),
                'metas' => [
                    '_tax_status',
                    '_tax_class',
                ],
            ],
            'price' => [
                'name' => __('Price Metas', 'woo-poly-integration'),
                'desc' => __('Note the last price field is the final price taking into account the effect of sale price ', 'woo-poly-integration'),
                'metas' => [
                    '_regular_price',
                    '_sale_price',
                    '_sale_price_dates_from',
                    '_sale_price_dates_to',
                    '_price',
                ],
            ],
        ]);

        if (false === $flat) {
            return $default;
        }

        foreach ($default as $ID => $value) {
            $metas = array_merge($metas, Settings::getOption(
                $ID,
                MetasList::getID(),
                $value['metas']
            ));
        }

        return array_values($metas);
    }

    public static function getDisabledProductMetaToCopy(array $metas = []): array
    {
        foreach (static::getProductMetaToCopy([], false) as $group) {
            $metas = array_merge($metas, $group['metas']);
        }

        return apply_filters(
            HooksInterface::PRODUCT_DISABLED_META_SYNC_FILTER,
            array_values(array_diff($metas, static::getProductMetaToCopy()))
        );
    }

    public function addFieldsLocker(): bool
    {
        if ('off' === Settings::getOption('fields-locker', Features::getID(), 'on')) {
            return false;
        }

        $metas = static::getProductMetaToCopy();
        $selectors = ['.insert'];

        if (in_array('_product_attributes', $metas)) {
            if (in_array('_custom_product_attributes', $metas)) {
                $selectors[] = '#product_attributes :input';
                $selectors[] = '#product_attributes .select2-selection';
            } else {
                $selectors[] = '#product_attributes div.taxonomy :input';
                $selectors[] = '#product_attributes .select2-selection';
            }
        } elseif (in_array('_custom_product_attributes', $metas)) {
            $selectors[] = '#product_attributes div.woocommerce_attribute:not(.taxonomy) :input';
        }

        $selectors = apply_filters(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, $selectors);
        $jsID = 'product-fields-locker';
        $code = sprintf(
            'function hyyan_wpi_lockFields(){ '
            . ' var disabled = %s;'
            . 'var disabledSelectors = %s;'
            . 'var metaSelectors = "";'
            . 'for (var i = 0; i < disabled.length; i++) {'
            . ' metaSelectors += (","'
            . ' + "." + disabled[i] + ","'
            . ' + "#" + disabled[i] + ","'
            . ' + "*[name^=\'"+disabled[i]+"\']"'
            . ' )'
            . ' }'
            . ' $(disabledSelectors + metaSelectors)'
            . ' .off("click")'
            . ' .on("click", function (e) {e.preventDefault()})'
            . ' .css({'
            . ' opacity: .5,'
            . ' \'pointer-events\': \'none\','
            . ' cursor: \'not-allowed\''
            . ' });'
            . '};'
            . 'hyyan_wpi_lockFields();'
            . '$(document).ajaxComplete(function(){'
            . ' hyyan_wpi_lockFields(); '
            . '});',
            json_encode($metas),
            !empty($selectors) ? json_encode(implode(',', $selectors)) : [rand()]
        );

        Utilities::jsScriptWrapper($jsID, $code);
        return true;
    }

    public function suppressInvalidDuplicatedSKUErrorMsg(bool $sku_found, int $product_id, string $sku): bool
    {
        if (!$sku_found) {
            return false;
        }

        if (!$product_id) {
            return $sku_found;
        }

        global $wpdb;
        $postids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT $wpdb->posts.ID
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                WHERE $wpdb->posts.post_type IN ('product', 'product_variation')
                AND $wpdb->posts.post_status NOT IN ('trash', 'auto-draft')
                AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
                AND $wpdb->postmeta.post_id <> %d",
                wp_slash($sku),
                $product_id
            )
        );

        $curlang = pll_get_post_language($product_id);
        if (!$curlang) {
            return $sku_found;
        }

        foreach ($postids as $post_id) {
            if ($post_id != $product_id && $curlang == pll_get_post_language($post_id)) {
                return true;
            }
        }

        return false;
    }
}
