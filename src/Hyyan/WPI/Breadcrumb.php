<?php

declare(strict_types=1);

namespace Hyyan\WPI;

class Breadcrumb
{
    public function __construct()
    {
        add_filter('woocommerce_get_breadcrumb', [$this, 'translateBreadcrumb'], 10, 2);
        add_filter('woocommerce_breadcrumb_home_url', [$this, 'translateBreadrumbHomeUrl'], 10, 1);
    }

    public function translateBreadcrumb(array $crumbs, \WC_Breadcrumb $breadcrumb): array
    {
        $shop_page_id = wc_get_page_id('shop');
        $shop_page_translated = pll_get_post($shop_page_id);

        if ($shop_page_translated &&
            is_page() &&
            !is_shop() &&
            !is_product_category() &&
            !is_product_tag() &&
            !is_product() &&
            !is_cart() &&
            !is_checkout() &&
            !is_account_page() &&
            intval(get_option('page_on_front')) !== $shop_page_id
        ) {
            $homecrumb = array_shift($crumbs);
            array_unshift(
                $crumbs,
                [
                    wp_strip_all_tags(get_the_title($shop_page_translated)),
                    get_permalink($shop_page_translated)
                ]
            );
            array_unshift($crumbs, $homecrumb);
        }

        return $crumbs;
    }

    public function translateBreadrumbHomeUrl(string $home): string
    {
        if (function_exists('pll_home_url')) {
            return pll_home_url();
        }

        return $home;
    }
}
