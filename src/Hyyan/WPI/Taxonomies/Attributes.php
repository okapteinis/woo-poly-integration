<?php

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Utilities;
use WooCommerce;
use Polylang;

class Attributes implements TaxonomiesInterface
{
    public function __construct()
    {
        add_action('init', [$this, 'manageAttrLablesTranslation'], 11, 2);
        add_filter('woocommerce_attribute_label', [$this, 'translateAttrLable']);
        add_action('admin_print_scripts', [$this, 'addAttrsTranslateLinks'], 100);
    }

    public function manageAttrLablesTranslation(): bool
    {
        global $polylang, $woocommerce;

        if (!$polylang || !$woocommerce) {
            return false;
        }

        $section = __('WooCommerce Attributes', 'woo-poly-integration');
        
        foreach (wc_get_attribute_taxonomies() as $attr) {
            pll_register_string(
                $attr->attribute_label,
                $attr->attribute_label,
                $section
            );
        }

        return true;
    }

    public function translateAttrLable(string $label): string
    {
        return pll__($label);
    }

    public function addAttrsTranslateLinks(): bool
    {
        global $pagenow;
        if ($pagenow !== 'edit.php') {
            return false;
        }

        if (!isset($_GET['page']) || 
            esc_attr($_GET['page']) !== 'product_attributes'
        ) {
            return false;
        }

        $stringTranslationURL = $this->getStringTranslationUrl();
        $this->outputTranslationScripts($stringTranslationURL);

        return true;
    }

    private function getStringTranslationUrl(): string
    {
        return add_query_arg(
            [
                'page' => 'mlang_strings',
                'group' => __('WooCommerce Attributes', 'woo-poly-integration'),
            ],
            admin_url('admin.php')
        );
    }

    private function outputTranslationScripts(string $stringTranslationURL): void
    {
        $buttonCode = sprintf(
            '$("<a href=\'%s\' class=\'button button-primary button-large\'>%s</a><br><br>")' .
            '.insertBefore(".attributes-table");',
            $stringTranslationURL,
            __('Translate Attributes Labels', 'woo-poly-integration')
        );

        $searchLinkCode = sprintf(
            '$(".attributes-table .row-actions").each(function () {
                var $this = $(this);
                var attrName = $this.parent().find("strong a").text();
                var attrTranslateUrl = "%s&s=" + attrName;
                var attrTranslateHref = 
                    "<span class=\'translate\'>" +
                    "| " +
                    "<a href=\'" + attrTranslateUrl + "\'>%s</a>" +
                    "</span>";
                $this.append(attrTranslateHref);
            });',
            $stringTranslationURL,
            __('Translate', 'woo-poly-integration')
        );

        Utilities::jsScriptWrapper('attrs-label-translation-button', $buttonCode);
        Utilities::jsScriptWrapper('attr-label-translate-search-link', $searchLinkCode);
    }

    public static function getNames(): array
    {
        return wc_get_attribute_taxonomy_names();
    }
}
