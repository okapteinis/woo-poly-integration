<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<h3>
    <span><?php esc_html_e('Support The Plugin', 'woo-poly-integration'); ?></span>
</h3>

<div class="inside">
    <?php
    echo wp_kses(
        __(
            'I will never ask you for donation, now or in the future,
            but the plugin still needs your support.
            please support by rating this plugin on
            Wordpress Repository,
            or by giving the plugin a star on Github.
            If you speak a language other than English, you can support the plugin by extending the translation list and your name will be added to the translators list',
            'woo-poly-integration'
        ),
        [
            'strong' => [],
            'a' => ['href' => []],
            'br' => [],
        ]
    );
    ?>
</div>
