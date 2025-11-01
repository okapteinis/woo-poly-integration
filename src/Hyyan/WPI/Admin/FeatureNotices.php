<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 * (c) 2025 Ojārs Kapteinis <ojars@kapteinis.lv>
 *
 * This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0
 * International License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-nc-nd/4.0/ or send a letter to Creative Commons,
 * PO Box 1866, Mountain View, CA 94042, USA.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Utilities;

/**
 * FeatureNotices.
 *
 * Display admin notices for new features in v1.7.0
 *
 * @author Ojārs Kapteinis <ojars@kapteinis.lv>
 */
class FeatureNotices
{
    /**
     * Option name for dismissed notices.
     */
    const DISMISSED_NOTICES_OPTION = 'wpi_dismissed_feature_notices';

    /**
     * Current version that has new features.
     */
    const FEATURE_VERSION = '1.7.0';

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('admin_notices', array($this, 'showFeatureNotices'));
        add_action('wp_ajax_wpi_dismiss_feature_notice', array($this, 'dismissNotice'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Show feature notices in admin.
     */
    public function showFeatureNotices()
    {
        // Only show on WooCommerce pages
        $screen = function_exists('get_current_screen') ? get_current_screen() : false;
        if (!$screen || strpos($screen->id, 'woocommerce') === false) {
            return;
        }

        $dismissed = get_option(self::DISMISSED_NOTICES_OPTION, array());

        // HPOS Notice
        if (!in_array('hpos_support', $dismissed, true)) {
            $this->showHposNotice();
        }

        // REST API Notice
        if (!in_array('rest_api_support', $dismissed, true) && !in_array('hpos_support', $dismissed, true)) {
            $this->showRestApiNotice();
        }
    }

    /**
     * Show HPOS compatibility notice.
     */
    protected function showHposNotice()
    {
        $is_hpos = Utilities::is_hpos_enabled();
        $hpos_status = $is_hpos ? 'enabled' : 'disabled';
        $hpos_icon = $is_hpos ? '✓' : 'ℹ';

        ?>
        <div class="notice notice-info is-dismissible wpi-feature-notice" data-notice-id="hpos_support">
            <h3><?php esc_html_e('WooCommerce Polylang Integration v1.7.0 - HPOS Support', 'woo-poly-integration'); ?></h3>
            <p>
                <strong><?php echo esc_html($hpos_icon); ?> HPOS Status:</strong>
                <?php printf(
                    esc_html__('High-Performance Order Storage is %s on your store.', 'woo-poly-integration'),
                    '<strong>' . esc_html($hpos_status) . '</strong>'
                ); ?>
            </p>
            <?php if ($is_hpos): ?>
                <p>
                    <?php esc_html_e('✓ Your orders are now using HPOS with full language support!', 'woo-poly-integration'); ?>
                </p>
                <p>
                    <em><?php esc_html_e('Note: If you have existing orders from before v1.7.0, run the migration tool to add language metadata.', 'woo-poly-integration'); ?></em>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hyyan-wpi-tools')); ?>" class="button button-secondary">
                        <?php esc_html_e('Run Migration Tool', 'woo-poly-integration'); ?>
                    </a>
                </p>
            <?php else: ?>
                <p>
                    <?php esc_html_e('To enable HPOS, go to WooCommerce → Settings → Advanced → Features and enable "High-Performance Order Storage".', 'woo-poly-integration'); ?>
                </p>
            <?php endif; ?>
            <p>
                <strong><?php esc_html_e('What\'s New in v1.7.0:', 'woo-poly-integration'); ?></strong>
            </p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li><?php esc_html_e('HPOS (High-Performance Order Storage) full compatibility', 'woo-poly-integration'); ?></li>
                <li><?php esc_html_e('WooCommerce Blocks support (Cart, Checkout, Product Collection)', 'woo-poly-integration'); ?></li>
                <li><?php esc_html_e('REST API v3 language filtering', 'woo-poly-integration'); ?></li>
                <li><?php esc_html_e('Block Theme & Site Editor support', 'woo-poly-integration'); ?></li>
                <li><?php esc_html_e('Language-aware order search', 'woo-poly-integration'); ?></li>
            </ul>
            <p>
                <a href="https://github.com/hyyan/woo-poly-integration/blob/nightly/CHANGELOG.md#170---2025-01-xx" target="_blank" class="button button-primary">
                    <?php esc_html_e('View Full Changelog', 'woo-poly-integration'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Show REST API support notice.
     */
    protected function showRestApiNotice()
    {
        ?>
        <div class="notice notice-success is-dismissible wpi-feature-notice" data-notice-id="rest_api_support">
            <h3><?php esc_html_e('New: REST API Language Support', 'woo-poly-integration'); ?></h3>
            <p>
                <?php esc_html_e('You can now filter WooCommerce REST API endpoints by language!', 'woo-poly-integration'); ?>
            </p>
            <p>
                <strong><?php esc_html_e('Example Usage:', 'woo-poly-integration'); ?></strong>
            </p>
            <code style="display: block; padding: 10px; background: #f0f0f0; margin: 10px 0;">
                GET /wp-json/wc/v3/products?lang=en
            </code>
            <p>
                <?php esc_html_e('Or use the header:', 'woo-poly-integration'); ?>
                <code>X-WC-Language: en</code>
            </p>
            <p>
                <a href="https://github.com/hyyan/woo-poly-integration/wiki/REST-API-Language-Support" target="_blank">
                    <?php esc_html_e('Read API Documentation', 'woo-poly-integration'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Dismiss a notice via AJAX.
     */
    public function dismissNotice()
    {
        check_ajax_referer('wpi_dismiss_notice', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';

        if (!$notice_id) {
            wp_send_json_error('Invalid notice ID');
        }

        $dismissed = get_option(self::DISMISSED_NOTICES_OPTION, array());

        if (!in_array($notice_id, $dismissed, true)) {
            $dismissed[] = $notice_id;
            update_option(self::DISMISSED_NOTICES_OPTION, $dismissed);
        }

        wp_send_json_success();
    }

    /**
     * Enqueue scripts for notice dismissal.
     *
     * @param string $hook Current admin page hook
     */
    public function enqueueScripts($hook)
    {
        if (strpos($hook, 'woocommerce') === false) {
            return;
        }

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document).on('click', '.wpi-feature-notice .notice-dismiss', function() {
                var noticeId = $(this).closest('.wpi-feature-notice').data('notice-id');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpi_dismiss_feature_notice',
                        notice_id: noticeId,
                        nonce: '<?php echo esc_js(wp_create_nonce('wpi_dismiss_notice')); ?>'
                    }
                });
            });
        });
        </script>
        <?php
    }
}
