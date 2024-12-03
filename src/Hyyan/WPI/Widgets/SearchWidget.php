<?php

declare(strict_types=1);

namespace Hyyan\WPI\Widgets;

use Polylang;

class SearchWidget
{
    public function __construct()
    {
        add_filter('get_search_form', [$this, 'getSearchForm'], 10, 1);
    }

    public function getSearchForm(string $form): string
    {
        $polylang = $GLOBALS['polylang'] ?? null;
        if (!$polylang instanceof Polylang) {
            return $form;
        }

        return $this->processSearchForm($form, $polylang);
    }

    private function processSearchForm(string $form, Polylang $polylang): string
    {
        if (isset($polylang->links_model) && $polylang->links_model->using_permalinks) {
            return $this->handlePermalinks($form, $polylang);
        }

        if (isset($polylang->curlang, $polylang->curlang->slug)) {
            return $this->handleNonPermalinks($form, $polylang);
        }

        return $form;
    }

    private function handlePermalinks(string $form, Polylang $polylang): string
    {
        if (!preg_match('#<form[^>]*action="([^"]*)"[^>]*>#', $form, $matches)) {
            return $form;
        }

        $action = $matches[1];
        $newAction = $polylang->curlang->home_url . (parse_url($action, PHP_URL_PATH) ?: '');
        return str_replace($action, $newAction, $form);
    }

    private function handleNonPermalinks(string $form, Polylang $polylang): string
    {
        $language = $polylang->curlang->slug;
        return str_replace(
            '</form>',
            '<input type="hidden" name="lang" value="' . esc_attr($language) . '" /></form>',
            $form
        );
    }
}
