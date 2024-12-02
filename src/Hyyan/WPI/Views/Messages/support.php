<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}

use Hyyan\WPI\Plugin;
?>

<div class="author-profile">
    <div class="author-avatar">
        <img src="https://www.gravatar.com/avatar/4f51a48cb8de5bb5c4c2bfcb9c6ef500?s=150" 
             alt="Hyyan Abo Fakher"
             class="gravatar" />
    </div>
    
    <div class="author-info">
        <h3>
            <?php esc_html_e(
                'Hyyan WooCommerce Polylang Integration Plugin',
                'woo-poly-integration'
            ); ?>
        </h3>
        
        <p><?php echo wp_kses_post(Plugin::getView('badges')); ?></p>
        
        <p>
            <?php
            echo wp_kses(
                __(
                    'Hello, my name is <b>Hyyan Abo Fakher</b>, and I am the developer 
                    of plugin <b>Hyyan WooCommerce Polylang Integration</b>.<br>
                    If you like this plugin, please write a few words about it
                    at the <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/woo-poly-integration">wordpress.org</a>
                    or <a target="_blank" href="https://twitter.com">twitter</a>
                    It will help other people
                    find this useful plugin more quickly.<br><b>Thank you!</b>',
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
            ?>
        </p>
        
        <hr>
        
        <?php echo wp_kses_post(Plugin::getView('social')); ?>
    </div>
</div>

<style>
.author-profile {
    display: flex;
    gap: 2rem;
    padding: 1rem;
}
.author-avatar {
    flex: 0 0 20%;
    text-align: center;
}
.author-avatar img {
    max-width: 150px;
    height: auto;
    padding: 2%;
}
.author-info {
    flex: 1;
    padding: 0 2%;
}
</style>
