<?php

namespace Hyyan\WPI;

class Ajax
{
    public function __construct()
    {
        add_filter('woocommerce_ajax_get_endpoint', array($this, 'filter_woocommerce_ajax_get_endpoint'), 10, 2);
    }

    /**
     * @param string $url
     * @param string $request
     * @return string
     */
    public function filter_woocommerce_ajax_get_endpoint(string $url, string $request): string
    {
        global $polylang;
        $lang = ($polylang->curlang) ? $polylang->curlang : $polylang->pref_lang;
        return parse_url($polylang->filters_links->links->get_home_url($lang), PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
    }
}
