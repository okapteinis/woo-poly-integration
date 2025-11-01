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

namespace Hyyan\WPI\Tools;

use Hyyan\WPI\Utilities;

/**
 * OrderMigration.
 *
 * Migration tool to add language metadata to existing orders for HPOS compatibility
 *
 * @author Ojārs Kapteinis <ojars@kapteinis.lv>
 */
class OrderMigration
{
    /**
     * Option name for migration status.
     */
    const MIGRATION_STATUS_OPTION = 'wpi_order_migration_status';

    /**
     * Batch size for processing orders.
     */
    const BATCH_SIZE = 50;

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addToolsPage'), 99);
        add_action('wp_ajax_wpi_migrate_orders', array($this, 'ajaxMigrateOrders'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Add tools page to WooCommerce menu.
     */
    public function addToolsPage()
    {
        add_submenu_page(
            'hyyan-wpi',
            __('Order Migration', 'woo-poly-integration'),
            __('Order Migration', 'woo-poly-integration'),
            'manage_options',
            'hyyan-wpi-tools',
            array($this, 'renderToolsPage')
        );
    }

    /**
     * Render the tools page.
     */
    public function renderToolsPage()
    {
        $is_hpos = Utilities::is_hpos_enabled();
        $migration_status = get_option(self::MIGRATION_STATUS_OPTION, array(
            'completed' => false,
            'processed' => 0,
            'total' => 0,
            'last_run' => null,
        ));

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WooCommerce Polylang Integration - Order Migration', 'woo-poly-integration'); ?></h1>

            <div class="card">
                <h2><?php esc_html_e('HPOS Order Language Migration', 'woo-poly-integration'); ?></h2>

                <p>
                    <?php esc_html_e('This tool migrates language information from legacy post-based orders to HPOS order metadata.', 'woo-poly-integration'); ?>
                </p>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><?php esc_html_e('HPOS Status:', 'woo-poly-integration'); ?></th>
                            <td>
                                <?php if ($is_hpos): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                    <strong><?php esc_html_e('Enabled', 'woo-poly-integration'); ?></strong>
                                <?php else: ?>
                                    <span class="dashicons dashicons-warning" style="color: orange;"></span>
                                    <strong><?php esc_html_e('Disabled', 'woo-poly-integration'); ?></strong>
                                    <p class="description">
                                        <?php esc_html_e('Migration is only needed when HPOS is enabled. Enable HPOS in WooCommerce → Settings → Advanced → Features.', 'woo-poly-integration'); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($migration_status['last_run']): ?>
                        <tr>
                            <th><?php esc_html_e('Last Migration:', 'woo-poly-integration'); ?></th>
                            <td>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $migration_status['last_run'])); ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($migration_status['total'] > 0): ?>
                        <tr>
                            <th><?php esc_html_e('Orders Processed:', 'woo-poly-integration'); ?></th>
                            <td>
                                <strong><?php echo esc_html($migration_status['processed']); ?></strong>
                                <?php esc_html_e('of', 'woo-poly-integration'); ?>
                                <strong><?php echo esc_html($migration_status['total']); ?></strong>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($is_hpos): ?>
                    <p>
                        <button type="button" id="wpi-start-migration" class="button button-primary">
                            <?php esc_html_e('Start Migration', 'woo-poly-integration'); ?>
                        </button>
                        <button type="button" id="wpi-stop-migration" class="button button-secondary" style="display: none;">
                            <?php esc_html_e('Stop Migration', 'woo-poly-integration'); ?>
                        </button>
                    </p>

                    <div id="wpi-migration-progress" style="display: none; margin-top: 20px;">
                        <h3><?php esc_html_e('Migration Progress', 'woo-poly-integration'); ?></h3>
                        <div style="background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                            <div id="wpi-progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s;"></div>
                            <span id="wpi-progress-text" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #333;"></span>
                        </div>
                        <p id="wpi-migration-status" style="margin-top: 10px;"></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2><?php esc_html_e('What This Tool Does', 'woo-poly-integration'); ?></h2>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><?php esc_html_e('Scans all existing orders in your store', 'woo-poly-integration'); ?></li>
                    <li><?php esc_html_e('Reads language information from Polylang post metadata', 'woo-poly-integration'); ?></li>
                    <li><?php esc_html_e('Adds language metadata to HPOS order tables', 'woo-poly-integration'); ?></li>
                    <li><?php esc_html_e('Processes orders in batches to avoid timeouts', 'woo-poly-integration'); ?></li>
                    <li><?php esc_html_e('Can be safely run multiple times', 'woo-poly-integration'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for order migration.
     */
    public function ajaxMigrateOrders()
    {
        check_ajax_referer('wpi_migrate_orders', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        $result = $this->migrateOrderBatch($offset);

        wp_send_json_success($result);
    }

    /**
     * Migrate a batch of orders.
     *
     * @param int $offset Offset for batch processing
     *
     * @return array Migration results
     */
    protected function migrateOrderBatch($offset)
    {
        global $wpdb;

        // Get total count on first run
        if ($offset === 0) {
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");

            update_option(self::MIGRATION_STATUS_OPTION, array(
                'completed' => false,
                'processed' => 0,
                'total' => $total,
                'last_run' => time(),
            ));
        } else {
            $status = get_option(self::MIGRATION_STATUS_OPTION);
            $total = $status['total'];
        }

        // Get batch of orders
        $order_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'shop_order'
            ORDER BY ID ASC
            LIMIT %d OFFSET %d",
            self::BATCH_SIZE,
            $offset
        ));

        $migrated = 0;
        $skipped = 0;

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);

            if (!$order) {
                $skipped++;
                continue;
            }

            // Check if already has language in HPOS meta
            if (Utilities::is_hpos_enabled()) {
                $existing_lang = $order->get_meta('_order_language', true);

                if ($existing_lang) {
                    $skipped++;
                    continue;
                }
            }

            // Get language from Polylang
            $lang = pll_get_post_language($order_id);

            if ($lang) {
                Utilities::set_order_language($order, $lang);
                $migrated++;
            } else {
                $skipped++;
            }
        }

        $processed = $offset + count($order_ids);
        $completed = $processed >= $total;

        // Update status
        update_option(self::MIGRATION_STATUS_OPTION, array(
            'completed' => $completed,
            'processed' => $processed,
            'total' => $total,
            'last_run' => time(),
        ));

        return array(
            'migrated' => $migrated,
            'skipped' => $skipped,
            'processed' => $processed,
            'total' => $total,
            'completed' => $completed,
        );
    }

    /**
     * Enqueue scripts for migration tool.
     *
     * @param string $hook Current admin page hook
     */
    public function enqueueScripts($hook)
    {
        if ($hook !== 'woocommerce-polylang-integration_page_hyyan-wpi-tools') {
            return;
        }

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var migrationInProgress = false;
            var currentOffset = 0;

            $('#wpi-start-migration').on('click', function() {
                if (migrationInProgress) {
                    return;
                }

                migrationInProgress = true;
                currentOffset = 0;

                $('#wpi-start-migration').hide();
                $('#wpi-stop-migration').show();
                $('#wpi-migration-progress').show();

                runMigrationBatch();
            });

            $('#wpi-stop-migration').on('click', function() {
                migrationInProgress = false;
                $('#wpi-stop-migration').hide();
                $('#wpi-start-migration').show();
                $('#wpi-migration-status').text('<?php echo esc_js(__('Migration stopped by user', 'woo-poly-integration')); ?>');
            });

            function runMigrationBatch() {
                if (!migrationInProgress) {
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpi_migrate_orders',
                        offset: currentOffset,
                        nonce: '<?php echo esc_js(wp_create_nonce('wpi_migrate_orders')); ?>'
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert('<?php echo esc_js(__('Migration failed', 'woo-poly-integration')); ?>: ' + response.data);
                            migrationInProgress = false;
                            $('#wpi-stop-migration').hide();
                            $('#wpi-start-migration').show();
                            return;
                        }

                        var data = response.data;
                        var percentage = (data.processed / data.total) * 100;

                        $('#wpi-progress-bar').css('width', percentage + '%');
                        $('#wpi-progress-text').text(Math.round(percentage) + '%');
                        $('#wpi-migration-status').text(
                            '<?php echo esc_js(__('Processed', 'woo-poly-integration')); ?> ' + data.processed + ' <?php echo esc_js(__('of', 'woo-poly-integration')); ?> ' + data.total + ' <?php echo esc_js(__('orders', 'woo-poly-integration')); ?> (' + data.migrated + ' <?php echo esc_js(__('migrated', 'woo-poly-integration')); ?>, ' + data.skipped + ' <?php echo esc_js(__('skipped', 'woo-poly-integration')); ?>)'
                        );

                        if (data.completed) {
                            migrationInProgress = false;
                            $('#wpi-stop-migration').hide();
                            $('#wpi-start-migration').show();
                            $('#wpi-migration-status').html('<strong><?php echo esc_js(__('Migration completed successfully!', 'woo-poly-integration')); ?></strong>');
                        } else {
                            currentOffset = data.processed;
                            setTimeout(runMigrationBatch, 500);
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Migration failed due to a server error', 'woo-poly-integration')); ?>');
                        migrationInProgress = false;
                        $('#wpi-stop-migration').hide();
                        $('#wpi-start-migration').show();
                    }
                });
            }
        });
        </script>
        <?php
    }
}
