<?php

declare(strict_types=1);

namespace Hyyan\WPI\Widgets;

class LayeredNav
{
    public function __construct()
    {
        add_filter('woocommerce_layered_nav_term_html', [$this, 'translateTerms'], 10, 4);
    }

    public function translateTerms(string $term_html, object $term, string $link, string $count): string
    {
        return str_replace($term->name, pll__($term->name), $term_html);
    }

    public function translateActiveFilters(): bool
    {
        if (empty($_GET)) {
            return false;
        }

        foreach (wc_get_attribute_taxonomies() as $tax) {
            $attribute = $tax->attribute_name;
            $taxonomy = wc_attribute_taxonomy_name($attribute);
            $name = 'filter_' . $attribute;

            if (empty($_GET[$name]) || !taxonomy_exists($taxonomy)) {
                continue;
            }

            $terms = explode(',', sanitize_text_field($_GET[$name]));
            $termsTranslations = $this->getTermTranslations($terms);
            $_GET[$name] = implode(',', $termsTranslations);
            $_chosen_attributes[$taxonomy]['terms'] = $termsTranslations;
        }

        return true;
    }

    private function getTermTranslations(array $terms): array
    {
        $termsTranslations = [];
        foreach ($terms as $ID) {
            $translation = pll_get_term($ID);
            $termsTranslations[] = $translation ?: $ID;
        }
        return $termsTranslations;
    }
}
