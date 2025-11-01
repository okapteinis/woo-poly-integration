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
 * RestAPI.
 *
 * Handle WooCommerce REST API v3 language support
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class RestAPI
{
    /**
     * Current language for API request.
     *
     * @var string
     */
    protected $api_language = '';

    /**
     * Construct object.
     */
    public function __construct()
    {
        // Set language from request
        add_filter('rest_request_before_callbacks', array($this, 'setLanguageFromRequest'), 10, 3);

        // Filter products by language
        add_filter('woocommerce_rest_product_object_query', array($this, 'filterProductsByLanguage'), 10, 2);
        add_filter('woocommerce_rest_product_query', array($this, 'addLanguageToProductQuery'), 10, 2);

        // Filter orders by language
        add_filter('woocommerce_rest_shop_order_object_query', array($this, 'filterOrdersByLanguage'), 10, 2);

        // Add language to product response
        add_filter('woocommerce_rest_prepare_product_object', array($this, 'addLanguageToProductResponse'), 10, 3);

        // Add language to order response
        add_filter('woocommerce_rest_prepare_shop_order_object', array($this, 'addLanguageToOrderResponse'), 10, 3);

        // Filter product categories by language
        add_filter('woocommerce_rest_product_cat_query', array($this, 'filterTermsByLanguage'), 10, 2);

        // Filter product tags by language
        add_filter('woocommerce_rest_product_tag_query', array($this, 'filterTermsByLanguage'), 10, 2);

        // Add language to category/tag response
        add_filter('woocommerce_rest_prepare_product_cat', array($this, 'addLanguageToTermResponse'), 10, 3);
        add_filter('woocommerce_rest_prepare_product_tag', array($this, 'addLanguageToTermResponse'), 10, 3);

        // Filter coupons by language
        add_filter('woocommerce_rest_shop_coupon_object_query', array($this, 'filterCouponsByLanguage'), 10, 2);
    }

    /**
     * Set language from REST API request.
     *
     * Supports both query parameter (?lang=en) and header (X-WC-Language: en)
     *
     * @param \WP_REST_Response|\WP_HTTP_Response|\WP_Error $response Response object
     * @param array                                          $handler  Route handler
     * @param \WP_REST_Request                               $request  Request object
     *
     * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error
     */
    public function setLanguageFromRequest($response, $handler, $request)
    {
        // Only process WooCommerce API requests
        if (strpos($request->get_route(), '/wc/') === false) {
            return $response;
        }

        // Try to get language from query parameter
        $lang = $request->get_param('lang');

        // Try header if not in query
        if (!$lang) {
            $lang = $request->get_header('X-WC-Language');
        }

        // Validate language
        if ($lang && in_array($lang, pll_languages_list(), true)) {
            $this->api_language = $lang;
            // Set Polylang current language for this request
            if (function_exists('PLL')) {
                PLL()->curlang = PLL()->model->get_language($lang);
            }
        }

        return $response;
    }

    /**
     * Filter products by language in REST API.
     *
     * @param array            $args    Query arguments
     * @param \WP_REST_Request $request Request object
     *
     * @return array Modified query arguments
     */
    public function filterProductsByLanguage($args, $request)
    {
        $lang = $this->getApiLanguage($request);

        if ($lang) {
            $args['lang'] = $lang;
        }

        return $args;
    }

    /**
     * Add language to product query tax query.
     *
     * @param array            $args    Query arguments
     * @param \WP_REST_Request $request Request object
     *
     * @return array Modified query arguments
     */
    public function addLanguageToProductQuery($args, $request)
    {
        $lang = $this->getApiLanguage($request);

        if ($lang) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = array();
            }

            $args['tax_query'][] = array(
                'taxonomy' => 'language',
                'field'    => 'slug',
                'terms'    => $lang,
            );
        }

        return $args;
    }

    /**
     * Filter orders by language in REST API.
     *
     * @param array            $args    Query arguments
     * @param \WP_REST_Request $request Request object
     *
     * @return array Modified query arguments
     */
    public function filterOrdersByLanguage($args, $request)
    {
        $lang = $this->getApiLanguage($request);

        if ($lang) {
            if (Utilities::is_hpos_enabled()) {
                // HPOS mode: filter by meta
                if (!isset($args['meta_query'])) {
                    $args['meta_query'] = array();
                }

                $args['meta_query'][] = array(
                    'key'     => '_order_language',
                    'value'   => $lang,
                    'compare' => '=',
                );
            } else {
                // Legacy mode: use language query
                $args['lang'] = $lang;
            }
        }

        return $args;
    }

    /**
     * Filter terms (categories/tags) by language in REST API.
     *
     * @param array            $args    Query arguments
     * @param \WP_REST_Request $request Request object
     *
     * @return array Modified query arguments
     */
    public function filterTermsByLanguage($args, $request)
    {
        $lang = $this->getApiLanguage($request);

        if ($lang) {
            $args['lang'] = $lang;
        }

        return $args;
    }

    /**
     * Filter coupons by language in REST API.
     *
     * @param array            $args    Query arguments
     * @param \WP_REST_Request $request Request object
     *
     * @return array Modified query arguments
     */
    public function filterCouponsByLanguage($args, $request)
    {
        $lang = $this->getApiLanguage($request);

        if ($lang) {
            $args['lang'] = $lang;
        }

        return $args;
    }

    /**
     * Add language information to product REST API response.
     *
     * @param \WP_REST_Response $response The response object
     * @param \WC_Product       $product  Product object
     * @param \WP_REST_Request  $request  Request object
     *
     * @return \WP_REST_Response
     */
    public function addLanguageToProductResponse($response, $product, $request)
    {
        $data = $response->get_data();

        // Add language code
        $lang = pll_get_post_language($product->get_id());
        $data['language'] = $lang;

        // Add translations
        $translations = Utilities::getProductTranslationsArrayByID($product->get_id());
        $data['translations'] = array();

        foreach ($translations as $lang_code => $product_id) {
            if ($product_id !== $product->get_id()) {
                $data['translations'][$lang_code] = $product_id;
            }
        }

        $response->set_data($data);

        return $response;
    }

    /**
     * Add language information to order REST API response.
     *
     * @param \WP_REST_Response $response The response object
     * @param \WC_Order         $order    Order object
     * @param \WP_REST_Request  $request  Request object
     *
     * @return \WP_REST_Response
     */
    public function addLanguageToOrderResponse($response, $order, $request)
    {
        $data = $response->get_data();

        // Add language code
        $lang = Utilities::get_order_language($order);
        $data['language'] = $lang;

        // Add language name if available
        if ($lang) {
            $lang_obj = Utilities::getLanguageEntity($lang);
            if ($lang_obj) {
                $data['language_name'] = $lang_obj->name;
            }
        }

        $response->set_data($data);

        return $response;
    }

    /**
     * Add language information to term (category/tag) REST API response.
     *
     * @param \WP_REST_Response $response The response object
     * @param \WP_Term          $term     Term object
     * @param \WP_REST_Request  $request  Request object
     *
     * @return \WP_REST_Response
     */
    public function addLanguageToTermResponse($response, $term, $request)
    {
        $data = $response->get_data();

        // Add language code
        $lang = pll_get_term_language($term->term_id);
        $data['language'] = $lang;

        // Add translations
        $translations = Utilities::getTermTranslationsArrayByID($term->term_id);
        $data['translations'] = array();

        foreach ($translations as $lang_code => $term_id) {
            if ($term_id !== $term->term_id) {
                $data['translations'][$lang_code] = $term_id;
            }
        }

        $response->set_data($data);

        return $response;
    }

    /**
     * Get API language from request.
     *
     * @param \WP_REST_Request $request Request object
     *
     * @return string|false Language code or false
     */
    protected function getApiLanguage($request)
    {
        if ($this->api_language) {
            return $this->api_language;
        }

        // Try query parameter
        $lang = $request->get_param('lang');

        // Try header
        if (!$lang) {
            $lang = $request->get_header('X-WC-Language');
        }

        // Validate
        if ($lang && in_array($lang, pll_languages_list(), true)) {
            return $lang;
        }

        return false;
    }
}
