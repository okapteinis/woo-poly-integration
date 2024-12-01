<?php

declare(strict_types=1);

use Hyyan\WPI\Tools\TranslationsDownloader;

add_action('woocommerce_system_status_report', 'wpi_status_report');

function wpi_status_report(): void
{
    ?>
    <table class="wc_status_table widefat" cellspacing="0">
        <thead>
            <tr>
                <th colspan="3" data-export-label="WooCommerce Polylang Integration">
                    <h2><?php esc_html_e('WooCommerce Polylang Integration', 'woo-poly-integration'); ?></h2>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td data-export-label="Language Locale">
                    <?php esc_html_e('Language Locale', 'woo-poly-integration'); ?>:
                </td>
                <td class="help">
                    <?php 
                    echo wc_help_tip(
                        esc_html__('The current language used by WordPress. Default = English', 'woocommerce')
                    ); 
                    ?>
                </td>
                <td><?php echo esc_html(get_locale()); ?></td>
            </tr>
            <tr>
                <td data-export-label="Polylang Language Locale">
                    <?php esc_html_e('Polylang Default Language Locale', 'woo-poly-integration'); ?>:
                </td>
                <td class="help">
                    <?php 
                    echo wc_help_tip(
                        esc_html__('The default language set in Polylang', 'woo-poly-integration')
                    ); 
                    ?>
                </td>
                <td><?php echo esc_html(pll_default_language('locale')); ?></td>
            </tr>
            <tr>
                <td data-export-label="Polylang Available Languages">
                    <?php esc_html_e('Polylang Available Languages', 'woo-poly-integration'); ?>:
                </td>
                <td class="help">
                    <?php 
                    echo wc_help_tip(
                        esc_html__('The available languages in Polylang', 'woo-poly-integration')
                    ); 
                    ?>
                </td>
                <td><?php echo getLanguagesStatus(); ?></td>
            </tr>
        </tbody>
    </table>
    <?php
}

function getLanguagesStatus(): string
{
    $output = '';
    $langs = pll_languages_list(['fields' => 'locale']);

    foreach ($langs as $langLocale) {
        $location = sprintf(
            '%splugins/woocommerce-%s.mo',
            trailingslashit(WP_LANG_DIR),
            $langLocale
        );
        
        $downloaded = TranslationsDownloader::isDownloaded($langLocale);
        $status = $downloaded 
            ? sprintf(' (WooCommerce translation file found OK at %s) ', $location)
            : sprintf(' Warning - missing WooCommerce translation file NOT found at %s ', $location);
            
        $output .= esc_html($langLocale . $status) . '<br/>';
    }

    return $output;
}
