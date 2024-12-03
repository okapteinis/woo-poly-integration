<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

echo wp_kses(
    __(
        'Hyyan Abo Fakher, and I am the developer of plugin Hyyan WooCommerce Polylang Integration. If you like this plugin, please write a few words about it at the wordpress.org or twitter It will help other people find this useful plugin more quickly. Thank you!',
        'woo-poly-integration'
    ),
    [
        'b' => [],
        'br' => [],
        'a' => [
            'href' => [],
            'target' => []
        ]
    ]
);
