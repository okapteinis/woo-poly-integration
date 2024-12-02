<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<div class="wpi-admin-wrapper">
    <?php $vars['self']->show_navigation(); ?>
    
    <div class="postbox wpi-settings-form">
        <?php $vars['self']->show_forms(); ?>
    </div>
</div>
