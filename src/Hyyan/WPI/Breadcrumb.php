<?php

namespace Hyyan\WPI;

use WC_Breadcrumb;

/**
 * Breadcrumb.
 *
 * Handle Breadcrumb translation
 */
class Breadcrumb
{
    public function __construct()
    {
        add_filter('woocommerce_breadcrumb_home_url', [$this, 'translateBreadrumbHomeUrl'], 10, 1);
        add_filter('woocommerce_get_breadcrumb', [$this, 'wpi_shopbreadcrumb'], 10, 2);
    }

    /**
     * @param array $crumbs
     * @param WC_Breadcrumb $breadcrumb
     * @return array
     */
    public function wpi_shopbreadcrumb(array $crumbs, WC_Breadcrumb $breadcrumb): array
    {
        $permalinks = wc_get_permalink_structure();
        $curLang = pll_current_language();
        $baseLang = pll_default_language();
