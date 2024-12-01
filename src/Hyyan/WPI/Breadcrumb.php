<?php

namespace Hyyan\WPI;

use WC_Breadcrumb;

/**
 * Breadcrumb.
 *
 * Handle Breadcrumb translation
 */
class Breadcrumb
{
    public function __construct()
    {
        add_filter('woocommerce_breadcrumb_home_url', [$this, 'translateBreadrumbHomeUrl'], 10, 1);
        add_filter('woocommerce_get_breadcrumb', [$this, 'wpi_shopbreadcrumb'], 10, 2);
    }

    /**
     * @param array $crumbs
     * @param WC_Breadcrumb $breadcrumb
     * @return array
     */
    public function wpi_shopbreadcrumb(array $crumbs, WC_Breadcrumb $breadcrumb): array
    {
        $permalinks = wc_get_permalink_structure();
        $curLang = pll_current_language();
        $baseLang = pll_default_language();
        
        if ($curLang !== $baseLang) {
            $shop_page_translated_id = wc_get_page_id('shop');
            $shop_page_translated = get_post($shop_page_translated_id);
            $shop_page_id = pll_get_post($shop_page_translated_id, $baseLang);
            $shop_page = get_post($shop_page_id);

            if ($shop_page_id && 
                $shop_page && 
                isset($permalinks['product_base']) && 
                strstr($permalinks['product_base'], '/' . $shop_page->post_name) && 
                intval(get_option('page_on_front')) !== $shop_page_id) {
                
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
        }
        return $crumbs;
    }

    /**
     * @param string $home
     * @return string
     */
    public function translateBreadrumbHomeUrl(string $home): string
    {
        if (function_exists('pll_home_url')) {
            return pll_home_url();
        }
        
        return $home;
    }
}
