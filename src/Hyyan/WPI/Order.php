<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use WC_Product;

class Order
{
    public function __construct()
    {
        $this->limitPolylangFeaturesForOrders();
        add_filter('pll_get_post_types', [$this, 'manageOrderTranslation']);
        add_action('woocommerce_new_order', [$this, 'saveOrderLanguage']);
        add_filter('woocommerce_order_get_items', [$this, 'translateProductsInOrdersDetails']);
        add_filter('woocommerce_my_account_my_orders_query', [$this, 'correctMyAccountOrderQuery']);
    }

    public function manageOrderTranslation(array $types): array
    {
        $options = get_option('polylang');
        $postTypes = $options['post_types'] ?? [];

        if (!in_array('shop_order', $postTypes, true)) {
            $options['post_types'][] = 'shop_order';
            update_option('polylang', $options);
        }

        $types[] = 'shop_order';
        return $types;
    }

    public function saveOrderLanguage(int $order): void
    {
        $current = pll_current_language();
        if ($current) {
            pll_set_post_language($order, $current);
        }
    }

    public function translateProductsInOrdersDetails(?WC_Product $product): ?WC_Product
    {
        if (!$product) {
            return null;
        }

        return Utilities::getProductTranslationByObject($product);
    }

    public function correctMyAccountOrderQuery(array $query): array
    {
        add_filter(
            'woocommerce_order_data_store_cpt_get_orders_query',
            [$this, 'correctGetOrderQuery'],
            10,
            2
        );

        $query['lang'] = implode(',', pll_languages_list());
        return $query;
    }

    public function correctGetOrderQuery(array $query, array $args): array
    {
        if (isset($args['lang'])) {
            $query['lang'] = $args['lang'];
        }
        return $query;
    }

    public function limitPolylangFeaturesForOrders(): void
    {
        add_action('current_screen', function (): void {
            $screen = function_exists('get_current_screen') ? get_current_screen() : false;
            if ($screen && $screen->post_type === 'shop_order') {
                add_action('admin_print_scripts', function (): void {
                    $jsID = 'order-translations-buttons';
                    $code = '$(".pll_icon_add,#post-translations").fadeOut()';
                    Utilities::jsScriptWrapper($jsID, $code);
                }, 100);
            }
        });
    }

    public static function getOrderLangauge(int $ID): string|false
    {
        return pll_get_post_language($ID);
    }
}
