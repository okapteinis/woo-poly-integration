<?php

declare(strict_types=1);

namespace Hyyan\WPI;

class Login
{
    public function __construct()
    {
        add_filter(
            'woocommerce_login_redirect',
            [$this, 'getLoginRedirectPermalink'],
            10,
            2
        );
    }

    public function getLoginRedirectPermalink(string $to): string
    {
        $ID = url_to_postid($to);
        $translatedID = pll_get_post($ID);

        return $translatedID ? get_permalink($translatedID) : $to;
    }
}
