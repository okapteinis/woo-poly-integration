<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;
use WC;
use WP;
use PLL;

class Endpoints
{
    protected array $endpoints = [];

    public function __construct()
    {
        $this->regsiterEndpointsTranslations();

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

    public function regsiterEndpointsTranslations(): bool
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

    public function getEndpointTranslation(string $endpoint): string
    {
        pll_register_string(
            $endpoint,
            $endpoint,
            static::getPolylangStringSection()
        );

        $this->endpoints[] = $endpoint;

        return pll__($endpoint);
    }

    public function updateRules(mixed $value): mixed
    {
        remove_filter(
            'pre_update_option_rewrite_rules',
            [$this, __FUNCTION__],
            100,
            2
        );
        $this->addEndpoints();
        flush_rewrite_rules();

        return $value;
    }

    public function addEndpoints(): void
    {
        $langs = pll_languages_list();
        foreach ($this->endpoints as $endpoint) {
            foreach ($langs as $lang) {
                add_rewrite_endpoint(
                    pll_translate_string($endpoint, $lang),
                    EP_ROOT | EP_PAGES
                );
            }
        }
    }

    public function rebuildUrl(string $endpoint, string $value = '', string $permalink = ''): string
    {
        if (get_option('permalink_structure')) {
            $query_string = '';
            if (strstr($permalink, '?')) {
                $query_string = '?' . parse_url($permalink, PHP_URL_QUERY);
                $permalink = current(explode('?', $permalink));
            }
            return trailingslashit($permalink) . $endpoint . '/' . $query_string;
        }
        
        return add_query_arg($endpoint, $value, $permalink);
    }

    public function correctPolylangSwitcherLinks(string $link, string $slug): string
    {
        global $wp;
        $endpoints = WC()->query->get_query_vars();
        foreach ($endpoints as $key => $value) {
            if (isset($wp->query_vars[$key])) {
                $link = str_replace(
                    $value,
                    pll_translate_string($key, $slug),
                    $link
                );
                break;
            }
        }

        return $link;
    }

    public function fixMyAccountLinkInMenus(?array $items = []): array
    {
        if (!function_exists('PLL')) {
            return $items ?? [];
        }

        $translations = PLL()->model->post->get_translations(
            wc_get_page_id('myaccount')
        );

        foreach ($items ?? [] as $item) {
            if (in_array($item->object_id, $translations, true)) {
                $vars = WC()->query->get_query_vars();
                foreach ($vars as $key => $value) {
                    if ($value && ($pos = strpos($item->url, $value)) !== false) {
                        $item->url = substr($item->url, 0, $pos);
                    }
                }
            }
        }

        return $items ?? [];
    }

    public function showFlashMessages(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : false;
        
        if ($screen && 
            $screen->id === 'woocommerce_page_wc-settings' && 
            isset($_GET['tab']) && 
            $_GET['tab'] === 'advanced'
        ) {
            FlashMessages::add(
                MessagesInterface::MSG_ENDPOINTS_TRANSLATION,
                Plugin::getView('Messages/endpointsTranslations')
            );
        }
    }

    public static function getPolylangStringSection(): string
    {
        return __('WooCommerce Endpoints', 'woo-poly-integration');
    }
}
