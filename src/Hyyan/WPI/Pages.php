<?php

namespace Hyyan\WPI;

use WP;

class Pages
{
    public function __construct()
    {
        $method = [$this, 'getPostTranslationID'];
        $pages = apply_filters(HooksInterface::PAGES_LIST, [
            'shop',
            'cart',
            'checkout',
            'terms',
            'myaccount',
        ]);

        foreach ($pages as $page) {
            add_filter(sprintf('woocommerce_get_%s_page_id', $page), $method);
            add_filter(sprintf('option_woocommerce_%s_page_id', $page), $method);
        }

        add_filter('pll_get_archive_url', [$this, 'translateShopUrl'], 10, 2);

        if (!is_admin()) {
            add_filter('parse_request', [$this, 'correctShopPage']);
        }

        add_filter('woocommerce_shortcode_products_query', [$this, 'addShortcodeLanguageFilter'], 10, 2);
        add_filter('shortcode_atts_product_categories', [$this, 'addShortcodeLanguageFilterCategories'], 10, 4);
    }

    public function getPostTranslationID(int $id): int
    {
        if (!function_exists('pll_get_post')) {
            return $id;
        }

        return pll_get_post($id) ?: $id;
    }

    public function correctShopPage(WP $wp): bool
    {
        $shopID = wc_get_page_id('shop');
        $shopOnFront = ('page' === get_option('show_on_front')) && 
            in_array(
                get_option('page_on_front'),
                PLL()->model->post->get_translations($shopID),
                true
            );

        $vars = ['pagename', 'page', 'name'];
        foreach ($vars as $var) {
            if (isset($wp->query_vars[$var])) {
                $shopOnFront = false;
                break;
            }
        }

        if (!$shopOnFront && !empty($wp->query_vars['pagename'])) {
            $shopPage = get_post($shopID);
            $page = explode('/', $wp->query_vars['pagename']);

            if (isset($shopPage->post_name) && 
                $shopPage->post_name === $page[count($page) - 1]
            ) {
                unset($wp->query_vars['page'], $wp->query_vars['pagename']);
                $wp->query_vars['post_type'] = 'product';
            }
        }

        return false;
    }

    public function translateShopUrl(string $url, string $language): string
    {
        if (!is_post_type_archive('product') || is_front_page()) {
            return $url;
        }

        $shopPageID = get_option('woocommerce_shop_page_id');
        $shopPage = get_post($shopPageID);

        if ($shopPage) {
            $shopPageTranslatedID = pll_get_post($shopPageID, $language);
            $shopPageTranslation = get_post($shopPageTranslatedID);

            if ($shopPageTranslation && $shopPage->post_name !== $shopPageTranslation->post_name) {
                return substr_replace(
                    $url,
                    $shopPageTranslation->post_name,
                    strrpos($url, $shopPage->post_name),
                    strlen($shopPage->post_name)
                );
            }
        }

        return $url;
    }

    public function addShortcodeLanguageFilter(array $query_args, array $atts): array
    {
        if (!empty($atts['ids'])) {
            $transIds = array_map('pll_get_post', explode(',', $atts['ids']));
            $query_args['post__in'] = $transIds;
            
            if (isset($query_args['p']) && $query_args['p'] !== $transIds) {
                unset($query_args['p']);
            }
            
            $atts['ids'] = implode(',', $transIds);
        } else {
            $query_args['lang'] = $query_args['lang'] ?? pll_current_language();
        }
        
        return $query_args;
    }

    public function addShortcodeLanguageFilterCategories(array $out, array $pairs, array $atts, string $shortcode): array
    {
        if (!empty($atts['ids'])) {
            $transIds = array_map('pll_get_term', explode(',', $atts['ids']));
            $out['ids'] = implode(',', $transIds);
        }

        if (!empty($atts['parent'])) {
            $transParent = pll_get_term($atts['parent']);
            if ($transParent) {
                $out['parent'] = $transParent;
            }
        }

        return $out;
    }
}
