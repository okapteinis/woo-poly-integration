<?php

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Plugin;
use WeDevs_Settings_API;

class Settings extends WeDevs_Settings_API
{
    public function __construct()
    {
        parent::__construct();
        add_action('admin_init', [$this, 'init']);
        add_action('admin_menu', [$this, 'registerMenu']);

        new Features();
        new MetasList();
    }

    public function init(): void
    {
        $this->set_sections($this->getSections());
        $this->set_fields($this->getFields());
        $this->admin_init();
    }

    public function registerMenu(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        add_options_page(
            __('Hyyan WooCommerce Polylang Integration', 'woo-poly-integration'),
            __('WooPoly', 'woo-poly-integration'),
            'delete_posts',
            'hyyan-wpi',
            [$this, 'outputPage']
        );
    }

    public function getSections(): array
    {
        return apply_filters(HooksInterface::SETTINGS_SECTIONS_FILTER, []);
    }

    public function getFields(): array
    {
        return apply_filters(HooksInterface::SETTINGS_FIELDS_FILTER, []);
    }

    public function outputPage(): void
    {
        echo Plugin::getView('admin', [
            'self' => $this,
        ]);
    }

    public static function getOption(string $option, string $section, string $default = ''): mixed
    {
        $options = get_option($section);

        if (!empty($options[$option])) {
            return $options[$option];
        }

        if (isset($options[$option])) {
            return [];
        }

        return $default;
    }
}
