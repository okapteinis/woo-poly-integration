<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Tools\TranslationsDownloader;
use RuntimeException;

class Language
{
    public function __construct()
    {
        add_action('load-settings_page_mlang', [$this, 'downlaodWhenPolylangAddLangauge']);
        add_action('pll_add_language', [$this, 'downlaodWhenPolylangAddLangauge']);
        add_action('woo-poly.settings.wpi-features_fields', [$this, 'addSettingFields']);
    }

    public function addSettingFields(array $fields): array
    {
        $fields[] = [
            'name' => 'language-downloader',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Translation Downloader', 'woo-poly-integration'),
            'desc' => __(
                'Download Woocommerce translations when a new polylang language is added',
                'woo-poly-integration'
            ),
        ];

        return $fields;
    }

    public function downlaodWhenPolylangAddLangauge(): bool
    {
        if ('off' === Settings::getOption('language-downloader', Features::getID(), 'on')) {
            return false;
        }

        if (!isset($_REQUEST['pll_action']) || 
            'add' !== esc_attr($_REQUEST['pll_action'] ?? '')
        ) {
            return false;
        }

        $name = esc_attr($_REQUEST['name'] ?? '');
        $locale = esc_attr($_REQUEST['locale'] ?? '');

        if ('en_us' === strtolower($locale)) {
            return true;
        }

        try {
            return TranslationsDownloader::download($locale, $name);
        } catch (RuntimeException $ex) {
            add_settings_error(
                'general',
                $ex->getCode(),
                $ex->getMessage()
            );

            return false;
        }
    }
}
