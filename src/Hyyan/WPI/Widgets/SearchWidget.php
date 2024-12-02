<?php

declare(strict_types=1);

namespace Hyyan\WPI\Widgets;

use Polylang;

class SearchWidget
{
    public function __construct()
    {
        add_filter('get_product_search_form', [$this, 'fixSearchForm']);
    }

    public function fixSearchForm(?string $form): string
    {
        if (!$form) {
            return '';
        }

        global $polylang;
        if (!($polylang instanceof Polylang)) {
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
        if (!preg_match('#<form.+>#', $form, $matches)) {
            return $form;
        }

        $old = reset($matches);
        $new = preg_replace(
            '#' . $polylang->links_model->home . '\/?#',
            $polylang->curlang->search_url,
            $old
        );

        return $new ? str_replace($old, $new, $form) : $form;
    }

    private function handleNonPermalinks(string $form, Polylang $polylang): string
    {
        return str_replace(
            '</form>',
            sprintf(
                '<input type="hidden" name="lang" value="%s" /></form>',
                esc_attr($polylang->curlang->slug)
            ),
            $form
        );
    }
}
