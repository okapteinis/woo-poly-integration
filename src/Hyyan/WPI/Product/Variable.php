<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use WC_Product;
use WC_Product_Variable;
use WP_Post;

class Variable
{
    public function __construct()
    {
        add_action('save_post_product', [$this, 'handleNewProduct'], 5, 3);
        add_action('woocommerce_after_product_object_save', [$this, 'after_product_save'], 10, 2);
        add_action('wp_ajax_woocommerce_remove_variations', [$this, 'removeVariations'], 9);

        add_filter(HooksInterface::PRODUCT_META_SYNC_FILTER, [$this, 'extendProductMetaList']);
        add_filter(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, [$this, 'extendFieldsLockerSelectors']);
        add_filter('woocommerce_variable_children_args', [$this, 'allow_variable_children'], 10, 3);

        if (is_admin()) {
            $this->handleVariableLimitation();
            $this->shouldDisableLangSwitcher();
        }
    }

    public function handleNewProduct(int $post_id, WP_Post $post, bool $update): bool
    {
        if (!$update && isset($_GET['from_post'])) {
            $this->duplicateVariations($post_id, $post, $update);
            $this->syncDefaultAttributes($post_id, $post, $update);
        }
        return true;
    }

    public function after_product_save(WC_Product $product, object $data_store): void
    {
        $productid = $product->get_id();
        $post = get_post($productid);
        $this->duplicateVariations($productid, $post, true);
        $this->syncDefaultAttributes($productid, $post, true);
    }

    public function allow_variable_children(array $args, WC_Product $product, bool $visible): array
    {
        $args['lang'] = '';
        return $args;
    }

    public function duplicateVariations(int $ID, WP_Post $post, bool $update): bool
    {
        static $last_id;
        
        if ($ID === $last_id) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'post-new.php'], true) || $post->post_type !== 'product') {
            return false;
        }

        $product = wc_get_product($ID);
        if (!$product) {
            return false;
        }

        if ($product->get_type() !== 'variable' && !isset($_GET['from_post'])) {
            return false;
        }

        $last_id = $ID;
        if ($product->get_parent_id()) {
            $product = wc_get_product($product->get_parent_id());
        }

        $from = null;
        $post_lang = pll_get_post_language($product->get_id());
        $def_lang = pll_default_language();

        if ($post_lang === $def_lang) {
            $from = $product;
        } else {
            if (isset($_GET['from_post'])) {
                $from = Utilities::getProductTranslationByID(
                    esc_attr($_GET['from_post']),
                    pll_default_language()
                );
            } else {
                $from = Utilities::getProductTranslationByObject(
                    $product,
                    pll_default_language()
                );
            }
        }

        if (!($from instanceof WC_Product_Variable)) {
            return false;
        }

        $langs = isset($_GET['new_lang']) ? [$_GET['new_lang']] : pll_languages_list();
        if (($key = array_search($def_lang, $langs, true)) !== false) {
            unset($langs[$key]);
        }

        remove_action('woocommerce_after_product_object_save', [$this, 'after_product_save'], 10);
        add_filter('woocommerce_hide_invisible_variations', fn() => false);

        foreach ($langs as $lang) {
            $variation = new Variation(
                $from,
                Utilities::getProductTranslationByObject($product, $lang)
            );
            $variation->duplicate();
        }

        add_action('woocommerce_after_product_object_save', [$this, 'after_product_save'], 10, 2);
        
        return true;
    }

    public function removeVariations(): void
    {
        if (isset($_POST['variation_ids'])) {
            $IDS = (array) $_POST['variation_ids'];
            foreach ($IDS as $ID) {
                Variation::deleteRelatedVariation($ID);
            }
        }
    }

    public function extendProductMetaList(array $metas): array
    {
        $metas['Variables'] = [
            'name' => __('Variables Metas', 'woo-poly-integration'),
            'desc' => __('Variable Product pricing Metas', 'woo-poly-integration'),
            'metas' => [
                '_min_variation_price',
                '_max_variation_price',
                '_min_price_variation_id',
                '_max_price_variation_id',
                '_min_variation_regular_price',
                '_max_variation_regular_price',
                '_min_regular_price_variation_id',
                '_max_regular_price_variation_id',
                '_min_variation_sale_price',
                '_max_variation_sale_price',
                '_min_sale_price_variation_id',
                '_max_sale_price_variation_id',
            ],
        ];
        return $metas;
    }

    public function extendFieldsLockerSelectors(array $selectors): array
    {
        $variable_exclude = ['[name^="variable_description"]'];
        $metas_nosync = Meta::getDisabledProductMetaToCopy();
        
        foreach ($metas_nosync as $meta_nosync) {
            switch ($meta_nosync) {
                case '_sku':
                case '_manage_stock':
                case '_stock':
                case '_low_stock_amount':
                case '_backorders':
                case '_stock_status':
                case '_sold_individually':
                case '_weight':
                case '_length':
                case '_width':
                case '_height':
                case '_tax_status':
                case '_tax_class':
                case '_regular_price':
                case '_sale_price':
                case '_sale_price_dates_from':
                case '_sale_price_dates_to':
                case '_download_limit':
                case '_download_expiry':
                case '_download_type':
                    $variable_exclude[] = '[name^="variable' . $meta_nosync . '"]';
                    break;
                case 'product_shipping_class':
                    $variable_exclude[] = '[name^="variable_shipping_class"]';
                    break;
                case '_virtual':
                case '_downloadable':
                    $variable_exclude[] = '[name^="variable_is' . $meta_nosync . '"]';
                    break;
            }
        }

        $variable_exclude = apply_filters(
            HooksInterface::FIELDS_LOCKER_VARIABLE_EXCLUDE_SELECTORS_FILTER,
            $variable_exclude
        );

        $selectors[] = '#variable_product_options :input:not(' . implode(',', $variable_exclude) . ')';
        return $selectors;
    }

    public function handleVariableLimitation(): bool
    {
        global $pagenow;
        if ($pagenow !== 'post-new.php') {
            return false;
        }

        if (isset($_GET['from_post'])) {
            return false;
        }

        if (pll_current_language() === pll_default_language()) {
            return false;
        }

        add_action('admin_print_scripts', function(): void {
            $jsID = 'variables-data';
            $code = sprintf(
                'var HYYAN_WPI_VARIABLES = {'
                . ' title : "%s" ,'
                . ' content : "%s" ,'
                . ' defaultLang : "%s"'
                . '};',
                __('Wrong Language For Variable Product', 'woo-poly-integration'),
                __("Variable product must be created in the default language first or things will get messy. Read more, to know why", 'woo-poly-integration'),
                pll_default_language()
            );
            Utilities::jsScriptWrapper($jsID, $code, false);
        });

        add_action('admin_enqueue_scripts', function(): void {
            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script(
                'woo-poly-variables',
                plugins_url('public/js/Variables' . $suffix . '.js', Hyyan_WPI_DIR),
                ['jquery', 'jquery-ui-core', 'jquery-ui-dialog'],
                \Hyyan\WPI\Plugin::getVersion(),
                true
            );
        }, 100);

        return true;
    }

    public function shouldDisableLangSwitcher(): void
    {
        add_action('current_screen', function(): void {
            $screen = function_exists('get_current_screen') ? get_current_screen() : false;
            if (!$screen || $screen->id !== 'settings_page_mlang') {
                return;
            }

            $count = wp_count_posts('product_variation');
            if (!($count && $count->publish > 0)) {
                return;
            }

            add_action('admin_print_scripts', function(): void {
                $jsID = 'disable-lang-switcher';
                $code = sprintf(
                    '$("#options-lang #default_lang")'
                    . '.css({'
                    . ' "opacity": .5,'
                    . ' "pointer-events": "none"'
                    . '});'
                    . ' $("#options-lang").prepend('
                    . ' "%s"'
                    . ');',
                    __('You can not change the default language because you are using variable products', 'woo-poly-integration')
                );
                Utilities::jsScriptWrapper($jsID, $code);
            }, 100);
        });
    }
}
