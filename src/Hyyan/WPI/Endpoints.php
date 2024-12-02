<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;

class Endpoints
{
    protected array $endpoints = [];

    public function __construct()
    {
        $this->registerEndpointsTranslations();  // Izlabots
        add_action('init', [$this, 'rewriteEndpoints'], 11);
        add_action('woocommerce_update_options', [$this, 'addEndpoints']);
        add_filter('pre_update_option_rewrite_rules', [$this, 'updateRules'], 100, 2);
        add_filter('pll_the_language_link', [$this, 'correctPolylangSwitcherLinks'], 10, 2);
        add_filter('wp_get_nav_menu_items', [$this, 'fixMyAccountLinkInMenus']);
        add_action('current_screen', [$this, 'showFlashMessages']);
    }

    public function rewriteEndpoints(): void
    {
        $this->addEndpoints();
    }

    public function registerEndpointsTranslations(): bool  // Izlabots
    {
        if (!function_exists('WC') || !function_exists('PLL')) {
            return false;
        }

        $vars = WC()->query->get_query_vars();
        foreach ($vars as $key => $value) {
            WC()->query->query_vars[$key] = $this->getEndpointTranslation($value);
        }

        return true;
    }

    [Pārējais kods paliek nemainīgs...]
}
