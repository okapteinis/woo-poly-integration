<?php

declare(strict_types=1);

namespace Hyyan\WPI;

class Ajax
{
    public function __construct()
    {
        add_filter('woocommerce_ajax_get_endpoint', [$this, 'getEndpoint'], 10, 2);
    }

    public function getEndpoint(string $url, string $request): string
    {
        global $polylang;

        if (!$polylang) {
            return $url;
        }

        $lang = isset($polylang->curlang) ? $polylang->curlang : $polylang->pref_lang;
        return parse_url(
            $polylang->filters_links->links->get_home_url($lang), 
            PHP_URL_PATH
        ) . '?' . parse_url($url, PHP_URL_QUERY);
    }
}
