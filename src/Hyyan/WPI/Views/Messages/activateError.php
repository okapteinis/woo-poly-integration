<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

use Hyyan\WPI\Plugin;
?>

<h3>
    <?php esc_html_e('Hyyan WooCommerce Polylang Integration Plugin', 'woo-poly-integration'); ?>
</h3>

<p>
    <?php
    echo wp_kses(
        sprintf(
            '%s %s <a href="%s">%s</a>.',
            esc_html__('The plugin can not function correctly, the plugin requires minimum plugin versions WooCommerce version 3 or higher and Polylang 2 or higher. Please configure Polylang by adding a language before activating WooCommerce Polylang Integration.', 'woo-poly-integration'),
            esc_html__('See also', 'woo-poly-integration'),
            'https://github.com/hyyan/woo-poly-integration/wiki/Installation',
            esc_html__('Installation Guide', 'woo-poly-integration')
        ),
        [
            'a' => [
                'href' => []
            ]
        ]
    );
    ?>
</p>

<hr>

<div class="plugin-versions">
    <?php esc_html_e('Plugins : ', 'woo-poly-integration'); ?>
    
    <a href="https://wordpress.org/plugins/woocommerce/">
        <?php 
        printf(
            '%s V%s',
            esc_html__('WooCommerce', 'woo-poly-integration'),
            esc_attr(Plugin::WOOCOMMERCE_VERSION)
        ); 
        ?>
    </a>
    |
    <a href="https://wordpress.org/plugins/polylang/">
        <?php 
        printf(
            '%s V%s',
            esc_html__('Polylang', 'woo-poly-integration'),
            esc_attr(Plugin::POLYLANG_VERSION)
        ); 
        ?>
    </a>
</div>
