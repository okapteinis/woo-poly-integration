<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

use Hyyan\WPI\Endpoints;
?>

<?php
echo wp_kses(
    sprintf(
        /* translators: %1$s is the URL, %2$s is the translated "Translate" text */
        esc_html__('You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%1$s">%2$s</a>', 'woo-poly-integration'),
        esc_url(add_query_arg(
            [
                'page' => 'mlang_strings',
                'group' => Endpoints::getPolylangStringSection(),
            ],
            admin_url('admin.php')
        )),
        esc_html__('Translate', 'woo-poly-integration')
    ),
    [
        'a' => [
            'href' => [],
            'target' => []
        ]
    ]
);
?>
