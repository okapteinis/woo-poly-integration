<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

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
        add_filter('woocommerce_variable_children_args', [$this, 'allow_variable_children'], 10, 3);
    }

    public function handleNewProduct(int $post_id, WP_Post $post, bool $update): void
    {
        if (!$update && isset($_GET['from_post'])) {
            $this->duplicateVariations($post_id, $post, $update);
            $this->syncDefaultAttributes($post_id, $post, $update);
        }
    }

    public function after_product_save(WC_Product $product, object $data_store): void
    {
        $productId = $product->get_id();
        $post = get_post($productId);
        if ($post) {
            $this->duplicateVariations($productId, $post, true);
            $this->syncDefaultAttributes($productId, $post, true);
        }
    }

    public function allow_variable_children(array $args, WC_Product $product, bool $visible): array
    {
        $args['lang'] = '';
        return $args;
    }

    public function duplicateVariations(int $ID, WP_Post $post, bool $update): bool
    {
        static $last_id;
        if ($ID === $last_id ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        ) {
            return false;
        }

        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'post-new.php'], true) ||
            $post->post_type !== 'product'
        ) {
            return false;
        }

        $product = wc_get_product($ID);
        if (!$product ||
            ($product->get_type() !== 'variable' && !isset($_GET['from_post']))
        ) {
            return false;
        }

        $last_id = $ID;
        if ($product->get_parent_id()) {
            $product = wc_get_product($product->get_parent_id());
        }

        $from = $this->getSourceProduct($product);
        if (!($from instanceof WC_Product_Variable)) {
            return false;
        }

        $this->processDuplication($from, $product);
        return true;
    }

    private function getSourceProduct(WC_Product $product): ?WC_Product
    {
        if (pll_get_post_language($product->get_id()) === pll_default_language()) {
            return $product;
        }

        return isset($_GET['from_post']) ?
            Utilities::getProductTranslationByID(esc_attr($_GET['from_post']), pll_default_language()) :
            Utilities::getProductTranslationByObject($product, pll_default_language());
    }

    private function processDuplication(WC_Product_Variable $from, WC_Product $product): void
    {
        $langs = isset($_GET['new_lang']) ? [$_GET['new_lang']] : pll_languages_list();
        $def_lang = pll_default_language();
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
    }
}
