<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Product\Variation;
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Utilities;
use WC_Product;
use WC_Product_Variation;

class Cart
{
    public const ADD_TO_CART_HANDLER_VARIABLE = 'wpi_variable';

    public function __construct()
    {
        add_filter('woocommerce_add_to_cart_product_id', [$this, 'addToCart'], 10, 1);
        add_filter('woocommerce_cart_item_product', [$this, 'translateCartItemProduct'], 10, 2);
        add_filter('woocommerce_cart_item_product_id', [$this, 'translateCartItemProductId'], 10, 1);
        add_filter('woocommerce_cart_item_permalink', [$this, 'translateCartItemPermalink'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'translateCartItemData'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'replaceCartFragmentsScript'], 100);
    }

    public function addToCart(int $ID): int
    {
        $result = $ID;
        $IDS = Utilities::getProductTranslationsArrayByID($ID);
        
        foreach (WC()->cart->get_cart() as $values) {
            $product = $values['data'];
            if (in_array($product->get_id(), $IDS, true)) {
                $result = $product->get_id();
                break;
            }
        }
        return $result;
    }

    public function translateCartItemProduct(WC_Product|WC_Product_Variation $cart_item_data, array $cart_item): WC_Product|WC_Product_Variation
    {
        $cart_product_id = $cart_item['product_id'] ?? 0;
        $cart_variation_id = $cart_item['variation_id'] ?? 0;
        $cart_item_data_translation = $cart_item_data;

        switch ($cart_item_data->get_type()) {
            case 'variation':
                $variation_translation = $this->getVariationTranslation($cart_variation_id);
                if ($variation_translation && $variation_translation->get_id() !== $cart_variation_id) {
                    $cart_item_data_translation = $variation_translation;
                }
                break;
            case 'simple':
            default:
                $product_translation = Utilities::getProductTranslationByID($cart_product_id);
                if ($product_translation && $product_translation->get_id() !== $cart_product_id) {
                    $cart_item_data_translation = $product_translation;
                }
                break;
        }

        if ($cart_item_data_translation->get_id() !== $cart_item_data->get_id()) {
            $cart_item_data_translation = apply_filters(
                HooksInterface::CART_SWITCHED_ITEM,
                $cart_item_data_translation,
                $cart_item_data,
                $cart_item
            );
        }
        return $cart_item_data_translation;
    }

    public function translateCartItemProductId(int $cart_product_id): int
    {
        $translation_id = pll_get_post($cart_product_id);
        return $translation_id ?: $cart_product_id;
    }

    public function translateCartItemPermalink(string $item_permalink, array $cart_item): string
    {
        $cart_variation_id = $cart_item['variation_id'] ?? 0;
        if ($cart_variation_id !== 0) {
            $variation_translation = $this->getVariationTranslation($cart_variation_id);
            return $variation_translation ? $variation_translation->get_permalink() : $item_permalink;
        }
        return $item_permalink;
    }

    public function translateCartItemData(array $item_data, array $cart_item): array
    {
        $cart_variation_id = $cart_item['variation_id'] ?? 0;
        if ($cart_variation_id === 0 || 
            ($cart_variation_id !== 0 && $this->getVariationTranslation($cart_variation_id) === false)) {
            return $item_data;
        }

        $item_data_translation = [];
        foreach ($item_data as $data) {
            $term_id = null;
            foreach ($cart_item['variation'] as $tax => $term_slug) {
                $tax = str_replace('attribute_', '', $tax);
                $term = get_term_by('slug', $term_slug, $tax);
                if ($term && isset($data['value']) && $term->name === $data['value']) {
                    $term_id = $term->term_id;
                    break;
                }
            }

            if ($term_id !== 0 && $term_id !== null) {
                $term_id_translation = pll_get_term($term_id);
                if ($term_id_translation === $term_id) {
                    $item_data_translation[] = $data;
                } else {
                    $term_translation = get_term($term_id_translation);
                    $error = $term_translation instanceof \WP_Error;
                    $item_data_translation[] = [
                        'key' => $data['key'],
                        'value' => !$error ? $term_translation->name : $data['value']
                    ];
                }
            } else {
                $item_data_translation[] = $data;
            }
        }
        return !empty($item_data_translation) ? $item_data_translation : $item_data;
    }

    public function replaceCartFragmentsScript(): void
    {
        wp_deregister_script('wc-cart-fragments');
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script(
            'wc-cart-fragments',
            plugins_url('public/js/Cart' . $suffix . '.js', Hyyan_WPI_DIR),
            ['jquery', 'jquery-cookie'],
            Plugin::getVersion(),
            true
        );
    }

    public function getVariationTranslation(int $variation_id, string $lang = ''): WC_Product_Variation|false
    {
        $variation = wc_get_product($variation_id);
        $parentid = Utilities::get_variation_parentid($variation);
        $_product_id = pll_get_post($parentid, $lang);
        $meta = get_post_meta($variation_id, Variation::DUPLICATE_KEY, true);

        if ($_product_id && $meta) {
            $variation_post = get_posts([
                'meta_key' => Variation::DUPLICATE_KEY,
                'meta_value' => $meta,
                'post_type' => 'product_variation',
                'post_parent' => $_product_id
            ]);

            if ($variation_post && count($variation_post) === 1) {
                return wc_get_product($variation_post[0]->ID);
            }
        }
        return false;
    }
}
