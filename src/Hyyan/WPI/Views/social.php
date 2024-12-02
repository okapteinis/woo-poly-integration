<?php declare(strict_types=1); ?>

<style>
.wpi-social-column {
    position: relative;
    float: left;
    padding-left: 1%;
}
.wpi-social-wrapper {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}
.wpi-social-clear {
    clear: both;
}
</style>

<?php
$social_scripts = [
    [
        'id' => 'facebook-jssdk',
        'src' => '//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.3&appId=1419909984943357'
    ],
    [
        'id' => 'twitter-wjs',
        'src' => 'https://platform.twitter.com/widgets.js'
    ]
];

foreach ($social_scripts as $script): ?>
    <script>
        (function(d, s, id) {
            if (d.getElementById(id)) return;
            var js = d.createElement(s);
            js.id = id;
            js.src = '<?php echo esc_url($script['src']); ?>';
            var fjs = d.getElementsByTagName(s)[0];
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', '<?php echo esc_attr($script['id']); ?>'));
    </script>
<?php endforeach; ?>

<div class="wpi-social-wrapper">
    <div class="wpi-social-column">
        <a href="https://twitter.com/share" 
           class="twitter-share-button" 
           data-url="<?php echo esc_url('https://wordpress.org/plugins/woo-poly-integration/'); ?>"
           data-text="<?php echo esc_attr('Hyyan WooCommerce Polylang Integration, makes you run multilingual store easily.'); ?>"
           data-via="HyyanAF">
            Tweet
        </a>
    </div>
    
    <div class="wpi-social-column">
        <div id="fb-root"></div>
        <div class="fb-share-button" 
             data-href="<?php echo esc_url('https://wordpress.org/plugins/woo-poly-integration/'); ?>"
             data-layout="button_count">
        </div>
    </div>
    
    <div class="wpi-social-column">
        <iframe src="<?php echo esc_url('https://ghbtns.com/github-btn.html?user=hyyan&repo=woo-poly-integration&type=star&count=true'); ?>"
                title="GitHub Stars"
                frameborder="0" 
                scrolling="0" 
                width="170" 
                height="20">
        </iframe>
    </div>
</div>

<div class="wpi-social-clear"></div>
