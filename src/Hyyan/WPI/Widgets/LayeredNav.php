<?php

declare(strict_types=1);

namespace Hyyan\WPI\Widgets;

use WC_Query;

class LayeredNav
{
    public function __construct()
    {
        add_action('init', [$this, 'layeredNavInit'], 1000);
    }

    public function layeredNavInit(): bool
    {
        if (!is_active_widget(false, false, 'woocommerce_layered_nav', true) || 
            is_admin()
        ) {
            return false;
        }

        global $_chosen_attributes;

        $attributes = wc_get_attribute_taxonomies();
        foreach ($attributes as $tax) {
            $attribute = wc_sanitize_taxonomy_name($tax->attribute_name);
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
