<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Utilities;

/**
 * Order.
 *
 * Handle order language
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Order
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        // Only register for legacy post-based orders if HPOS is not enabled
        if (!Utilities::is_hpos_enabled()) {
            /* Manage order translation */
            add_filter(
                'pll_get_post_types', array($this, 'manageOrderTranslation')
            );
        }

        /* Save the order language with every checkout */
        add_action(
            'woocommerce_checkout_update_order_meta', array($this, 'saveOrderLanguage')
        );

        if (is_admin()) {
            $this->limitPolylangFeaturesForOrders();

            // Add language column to HPOS orders list
            if (Utilities::is_hpos_enabled()) {
                add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'addLanguageColumn'));
                add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'renderLanguageColumn'), 10, 2);
            }

            // Add language-aware order search filtering
            add_filter('woocommerce_shop_order_search_fields', array($this, 'addOrderSearchLanguageFilter'));
            add_filter('woocommerce_order_query', array($this, 'filterOrderSearchByLanguage'), 10, 2);
        }

        /* For the query used to get orders in my account page */
        add_filter('woocommerce_my_account_my_orders_query', array($this, 'correctMyAccountOrderQuery'));

        /* Translate products in order details */
        add_filter(
            'woocommerce_order_item_product', array($this, 'translateProductsInOrdersDetails'), 10, 3
        );
    }

    /**
     * Notifty polylang about order custom post.
     *
     * @param array $types array of custom post names managed by polylang
     *
     * @return array
     */
    public function manageOrderTranslation(array $types)
    {
        $options   = get_option('polylang');
        $postTypes = $options['post_types'];
        if (!in_array('shop_order', $postTypes, true)) {
            $options['post_types'][] = 'shop_order';
            update_option('polylang', $options);
        }

        $types [] = 'shop_order';

        return $types;
    }

    /**
     * Save the order language with every checkout.
     *
     * @param int|\WC_Order $order the order ID or object
     */
    public function saveOrderLanguage($order)
    {
        $current = pll_current_language();
        if ($current) {
            Utilities::set_order_language($order, $current);
        }
    }

    /**
     * Translate products in order details pages.
     *
     * @param \WC_Product $product
     *
     * @return \WC_Product
     */
    public function translateProductsInOrdersDetails($product)
    {
        if ($product) {
            return Utilities::getProductTranslationByObject($product);
        } else {
            return false;
        }
    }

    /**
     * Correct My account order query.
     *
     * Will correct the query to display orders from all languages
     *
     * @param array $query  query arguments
     *
     * @return array
     */
    public function correctMyAccountOrderQuery(array $query)
    {
        if (Utilities::is_hpos_enabled()) {
            // HPOS mode: no language filtering needed as meta queries handle it
            // The orders will be filtered by customer_id automatically
        } else {
            // Legacy mode: use Polylang's language query
            add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'correctGetOrderQuery'), 10, 2);
            $query['lang'] = implode(',', pll_languages_list());
        }

        return $query;
    }

    /**
     * Correct wc_get_orders query for the My Account view orders page.
     *
     * Will correct the query to display orders from all languages
     *
     * @param array $query  WP_Query arguments
     * @param array $args   wc_get_orders query args
     *
     * @return array
     */
    public function correctGetOrderQuery($query, $args)
    {
        if (isset($args['lang'])) {
            $query['lang'] = $args['lang'];
        }
        return $query;
    }

    /**
     * Disallow the user to create translations for this post type.
     */
    public function limitPolylangFeaturesForOrders()
    {
        add_action('current_screen', function () {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

            if ( $screen && $screen->post_type === 'shop_order' ) {
                add_action('admin_print_scripts', function () {
                    $jsID = 'order-translations-buttons';
                    $code = '$(".pll_icon_add,#post-translations").fadeOut()';

                    Utilities::jsScriptWrapper($jsID, $code);
                }, 100);
            }
        });
    }

    /**
     * Get the order language.
     *
     * @param int|\WC_Order $ID order ID or order object
     *
     * @return string|false language on success, false otherwise
     */
    public static function getOrderLangauge($ID)
    {
        return Utilities::get_order_language($ID);
    }

    /**
     * Add language column to HPOS orders list in admin.
     *
     * @param array $columns Existing columns
     *
     * @return array Modified columns
     */
    public function addLanguageColumn($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            // Add language column after order number
            if ($key === 'order_number') {
                $new_columns['order_language'] = __('Language', 'woo-poly-integration');
            }
        }
        return $new_columns;
    }

    /**
     * Render language column content for HPOS orders list.
     *
     * @param string $column Column name
     * @param int|\WC_Order $order Order ID or object
     */
    public function renderLanguageColumn($column, $order)
    {
        if ($column === 'order_language') {
            $language = Utilities::get_order_language($order);
            if ($language) {
                $lang_object = Utilities::getLanguageEntity($language);
                if ($lang_object) {
                    echo esc_html($lang_object->name);
                } else {
                    echo esc_html($language);
                }
            } else {
                echo '—';
            }
        }
    }

    /**
     * Add language filter to order search (placeholder for future enhancement).
     *
     * @param array $search_fields Existing search fields
     *
     * @return array Modified search fields
     */
    public function addOrderSearchLanguageFilter($search_fields)
    {
        // This maintains the existing search fields
        // Language filtering happens in filterOrderSearchByLanguage
        return $search_fields;
    }

    /**
     * Filter order search results by current admin language.
     *
     * @param array $query Query arguments
     * @param array $query_vars Query variables
     *
     * @return array Modified query arguments
     */
    public function filterOrderSearchByLanguage($query, $query_vars)
    {
        // Only filter in admin order list
        if (!is_admin() || !isset($_GET['s']) || empty($_GET['s'])) {
            return $query;
        }

        // Check if we're on the orders page
        $screen = function_exists('get_current_screen') ? get_current_screen() : false;
        if (!$screen || ($screen->id !== 'edit-shop_order' && $screen->id !== 'woocommerce_page_wc-orders')) {
            return $query;
        }

        // Get current admin language or use Polylang filter
        $admin_lang = pll_current_language();

        if (!$admin_lang) {
            return $query;
        }

        if (Utilities::is_hpos_enabled()) {
            // HPOS mode: add meta query
            if (!isset($query['meta_query'])) {
                $query['meta_query'] = array();
            }

            $query['meta_query'][] = array(
                'key'     => '_order_language',
                'value'   => $admin_lang,
                'compare' => '=',
            );
        } else {
            // Legacy mode: use Polylang language query
            $query['lang'] = $admin_lang;
        }

        return $query;
    }
}
