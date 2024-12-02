<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

use Hyyan\WPI\Plugin;
?>

<h3>
    <span><?php esc_html_e('About The Plugin', 'woo-poly-integration'); ?></span>
</h3>

<div class="inside">
    <div>
        <?php
        printf(
            '%s. %s <a target="_blank" rel="noopener noreferrer" href="%s">%s</a>.',
            sprintf(
                esc_html__('The plugin is an open source project which aims to fill the gap between %1$s and %2$s', 'woo-poly-integration'),
                '<a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>',
                '<a href="https://wordpress.org/plugins/polylang/">Polylang</a>'
            ),
            esc_html__('For more information please see:', 'woo-poly-integration'),
            'https://github.com/hyyan/woo-poly-integration/wiki',
            esc_html__('documentation pages', 'woo-poly-integration')
        );
        ?>

        <p class="author-info">
            <?php
            printf(
                '%s <a href="%s" rel="noopener noreferrer">%s</a>',
                esc_html__('Author : ', 'woo-poly-integration'),
                'https://github.com/hyyan',
                'Hyyan Abo Fakher'
            );
            ?>
        </p>

        <?php echo wp_kses_post(Plugin::getView('badges')); ?>
    </div>
</div>
