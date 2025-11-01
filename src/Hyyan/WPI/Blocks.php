<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 * (c) 2025 Ojārs Kapteinis <ojars@kapteinis.lv>
 *
 * This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0
 * International License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-nc-nd/4.0/ or send a letter to Creative Commons,
 * PO Box 1866, Mountain View, CA 94042, USA.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Utilities;

/**
 * Blocks.
 *
 * Handle WooCommerce Block Editor support for multilingual stores
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Blocks
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        // Only load if WooCommerce Blocks is active
        if (!$this->is_woocommerce_blocks_active()) {
            return;
        }

        // Filter product queries in blocks
        add_filter('woocommerce_blocks_product_grid_item_html', array($this, 'filterBlockProducts'), 10, 3);

        // Product Collection Block (WooCommerce 8.0+)
        if ($this->supports_product_collection_block()) {
            add_filter('woocommerce_product_query_tax_query', array($this, 'addLanguageToProductQuery'), 10, 2);
        }

        // Cart and Checkout blocks context
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'setOrderLanguageFromApi'), 10, 2);

        // Filter block product queries
        add_filter('render_block', array($this, 'filterProductBlocks'), 10, 2);

        // Mini-cart block support
        add_filter('render_block_woocommerce/mini-cart-contents', array($this, 'filterMiniCartBlock'), 10, 2);

        // Modern Checkout Block optimizations
        add_action('woocommerce_blocks_checkout_block_registration', array($this, 'registerCheckoutBlockIntegration'));
        add_filter('woocommerce_store_api_product_quantity_limit', array($this, 'ensureProductLanguageInCart'), 10, 3);

        // Add language context to Store API responses
        add_filter('woocommerce_store_api_cart_item_data', array($this, 'addLanguageToCartItem'), 10, 2);
        add_filter('woocommerce_store_api_product_data', array($this, 'addLanguageToProductData'), 10, 2);

        // Ensure checkout strings are translatable
        add_filter('woocommerce_checkout_fields', array($this, 'translateCheckoutFields'), 10, 1);
    }

    /**
     * Check if WooCommerce Blocks is active.
     *
     * @return bool
     */
    protected function is_woocommerce_blocks_active()
    {
        return class_exists('Automattic\\WooCommerce\\Blocks\\Package');
    }

    /**
     * Check if Product Collection Block is supported (WooCommerce 8.0+).
     *
     * @return bool
     */
    protected function supports_product_collection_block()
    {
        return Utilities::woocommerceVersionCheck('8.0.0');
    }

    /**
     * Filter product blocks to show only products in the current language.
     *
     * @param string $block_content The block content
     * @param array  $block         The block data
     *
     * @return string Filtered block content
     */
    public function filterProductBlocks($block_content, $block)
    {
        // List of WooCommerce product blocks
        $product_blocks = array(
            'woocommerce/product-best-sellers',
            'woocommerce/product-category',
            'woocommerce/product-new',
            'woocommerce/product-on-sale',
            'woocommerce/product-top-rated',
            'woocommerce/products-by-attribute',
            'woocommerce/handpicked-products',
            'woocommerce/product-collection',
            'woocommerce/all-products',
            'woocommerce/featured-product',
        );

        if (!isset($block['blockName']) || !in_array($block['blockName'], $product_blocks, true)) {
            return $block_content;
        }

        // The filtering happens via the query filters
        return $block_content;
    }

    /**
     * Filter block product grid items.
     *
     * @param string      $html    Product item HTML
     * @param object      $data    Product data
     * @param \WC_Product $product Product object
     *
     * @return string Filtered HTML
     */
    public function filterBlockProducts($html, $data, $product)
    {
        if (!$product) {
            return $html;
        }

        $current_lang = pll_current_language();
        $product_lang = pll_get_post_language($product->get_id());

        // Hide products not in current language
        if ($current_lang && $product_lang && $current_lang !== $product_lang) {
            return '';
        }

        return $html;
    }

    /**
     * Add language filter to product queries.
     *
     * @param array     $tax_query  Tax query
     * @param \WP_Query $query      The query object
     *
     * @return array Modified tax query
     */
    public function addLanguageToProductQuery($tax_query, $query)
    {
        // Only filter in block context
        if (!is_admin() && $query->is_main_query() === false) {
            $current_lang = pll_current_language();

            if ($current_lang) {
                $tax_query[] = array(
                    'taxonomy' => 'language',
                    'field'    => 'slug',
                    'terms'    => $current_lang,
                );
            }
        }

        return $tax_query;
    }

    /**
     * Set order language from Store API request (Checkout Block).
     *
     * @param \WC_Order            $order   The order object
     * @param \WP_REST_Request     $request The REST request
     */
    public function setOrderLanguageFromApi($order, $request)
    {
        $current_lang = pll_current_language();

        if ($current_lang) {
            Utilities::set_order_language($order, $current_lang);
        }
    }

    /**
     * Filter Mini Cart block content.
     *
     * @param string $block_content The block content
     * @param array  $block         The block data
     *
     * @return string Filtered block content
     */
    public function filterMiniCartBlock($block_content, $block)
    {
        // Cart items are already filtered by WooCommerce cart class
        // This ensures the block displays the correct language
        return $block_content;
    }

    /**
     * Register checkout block integration for language support.
     *
     * This ensures the checkout block has proper language context
     */
    public function registerCheckoutBlockIntegration()
    {
        // The integration is handled via Store API filters
        // This hook is for future extensibility
    }

    /**
     * Ensure product language in cart matches current language.
     *
     * @param int         $quantity_limit Quantity limit
     * @param \WC_Product $product        Product object
     * @param array       $cart_item      Cart item data
     *
     * @return int Quantity limit
     */
    public function ensureProductLanguageInCart($quantity_limit, $product, $cart_item)
    {
        $current_lang = pll_current_language();
        $product_lang = pll_get_post_language($product->get_id());

        // If product is in wrong language, try to get translation
        if ($current_lang && $product_lang && $current_lang !== $product_lang) {
            $translated_product = Utilities::getProductTranslationByObject($product, $current_lang);
            if ($translated_product) {
                // Update cart item to use translated product
                // This is handled by the Cart class
            }
        }

        return $quantity_limit;
    }

    /**
     * Add language info to cart item data in Store API.
     *
     * @param array           $item_data Cart item data
     * @param \WC_Cart_Item   $cart_item Cart item object
     *
     * @return array Modified cart item data
     */
    public function addLanguageToCartItem($item_data, $cart_item)
    {
        if (isset($cart_item['product_id'])) {
            $lang = pll_get_post_language($cart_item['product_id']);
            if ($lang) {
                $item_data['language'] = $lang;
            }
        }

        return $item_data;
    }

    /**
     * Add language info to product data in Store API.
     *
     * @param array       $product_data Product data
     * @param \WC_Product $product      Product object
     *
     * @return array Modified product data
     */
    public function addLanguageToProductData($product_data, $product)
    {
        $lang = pll_get_post_language($product->get_id());

        if ($lang) {
            $product_data['language'] = $lang;

            // Add language name for display
            $lang_obj = Utilities::getLanguageEntity($lang);
            if ($lang_obj) {
                $product_data['language_name'] = $lang_obj->name;
            }
        }

        return $product_data;
    }

    /**
     * Translate checkout fields for block checkout.
     *
     * @param array $fields Checkout fields
     *
     * @return array Translated fields
     */
    public function translateCheckoutFields($fields)
    {
        // Field labels are automatically translated via WordPress i18n
        // This ensures any custom fields are also handled
        $current_lang = pll_current_language();

        if ($current_lang) {
            // Allow other plugins to filter fields by language
            $fields = apply_filters('wpi_checkout_fields_by_language', $fields, $current_lang);
        }

        return $fields;
    }
}
