<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use RuntimeException;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

class Language
{
    public function __construct()
    {
        add_action('wp_loaded', [$this, 'downloadWhenPolylangAddLanguage']);
    }

    public function downloadWhenPolylangAddLanguage(): bool
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

    protected function getFields(): array
    {
        $fields = [
            [
                'name' => 'language-downloader',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translation Downloader', 'woo-poly-integration'),
                'desc' => __(
                    'Download Woocommerce translations when a new polylang language is added',
                    'woo-poly-integration'
                ),
            ],
        ];

        return $fields;
    }
}
