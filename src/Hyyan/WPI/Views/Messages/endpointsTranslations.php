<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

use Hyyan\WPI\Endpoints;

echo wp_kses(
    sprintf(
        '%s <a href="%s" target="_blank">%s</a>',
        esc_html__('To translate endpoints labels please visit', 'woo-poly-integration'),
        esc_url(add_query_arg([
            'page' => 'mlang_strings',
            'group' => Endpoints::getPolylangStringSection(),
        ], admin_url('admin.php'))),
        esc_html__('Translate', 'woo-poly-integration')
    ),
    [
        'a' => [
            'href' => [],
            'target' => []
        ]
    ]
);
