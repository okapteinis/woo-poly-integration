<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<h3>
    <span><?php esc_html_e('Need Help ?', 'woo-poly-integration'); ?></span>
</h3>

<div class="inside">
    <p>
        <?php esc_html_e(
            'Need help? Want to ask for new features? please contact me using one of the following methods',
            'woo-poly-integration'
        ); ?>
    </p>
    
    <ol>
        <li>
            <a href="https://github.com/hyyan/woo-poly-integration/issues" 
               target="_blank" 
               rel="noopener noreferrer">
                <?php esc_html_e('On Github', 'woo-poly-integration'); ?>
            </a>
        </li>
        <li>
            <a href="mailto:hyyanaf@gmail.com" 
               target="_blank" 
               rel="noopener noreferrer">
                <?php esc_html_e('On Email', 'woo-poly-integration'); ?>
            </a>
        </li>
    </ol>
</div>
