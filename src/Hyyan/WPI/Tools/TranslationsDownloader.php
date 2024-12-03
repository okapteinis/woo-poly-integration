<?php

declare(strict_types=1);

namespace Hyyan\WPI\Tools;

use Hyyan\WPI\HooksInterface;
use RuntimeException;
use WP_Filesystem;

class TranslationsDownloader
{
    public static function download(string $locale, string $name): bool
    {
        $cantDownload = sprintf(
            __('Unable to download WooCommerce translation %s from : %2$s', 'woo-poly-integration'),
            sprintf('%s(%s)', $name, $locale),
            self::getRepoUrl()
        );

        $response = wp_remote_get(
            sprintf('%s/%s.zip', self::getRepoUrl(), $locale),
            ['sslverify' => false, 'timeout' => 200]
        );

        if (is_wp_error($response) ||
            $response['response']['code'] < 200 ||
            $response['response']['code'] >= 300
        ) {
            throw new RuntimeException($cantDownload);
        }

        return self::processDownload($response, $locale, $cantDownload);
    }

    private static function processDownload(array $response, string $locale, string $errorMessage): bool
    {
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            $creds = request_filesystem_credentials('', '', false, false, null);
            if ($creds === false || !WP_Filesystem($creds)) {
                throw new RuntimeException($errorMessage);
            }
        }

        $uploadDir = wp_upload_dir();
        $file = trailingslashit($uploadDir['path']) . $locale . '.zip';
        if (!$wp_filesystem->put_contents($file, $response['body'], FS_CHMOD_FILE)) {
            throw new RuntimeException($errorMessage);
        }

        $dir = trailingslashit(WP_LANG_DIR) . 'plugins/';
        $unzip = unzip_file($file, $dir);
        if ($unzip !== true) {
            throw new RuntimeException($errorMessage);
        }

        $wp_filesystem->delete($file);
        return true;
    }

    public static function isAvaliable(string $locale): bool
    {
        $response = wp_remote_get(
            sprintf('%s/%s.zip', self::getRepoUrl(), $locale),
            ['sslverify' => false, 'timeout' => 200]
        );
        return !is_wp_error($response) &&
            $response['response']['code'] >= 200 &&
            $response['response']['code'] < 300;
    }

    public static function isDownloaded(string $locale): bool
    {
        return file_exists(sprintf(
            '%splugins/woocommerce-%s.mo',
            trailingslashit(WP_LANG_DIR),
            $locale
        ));
    }

    public static function getRepoUrl(): string
    {
        $url = sprintf(
            'https://downloads.wordpress.org/translation/plugin/woocommerce/%s',
            WC()->version
        );
        return apply_filters(HooksInterface::LANGUAGE_REPO_URL_FILTER, $url);
    }
}
