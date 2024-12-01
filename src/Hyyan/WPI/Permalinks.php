<?php

namespace Hyyan\WPI;

class Permalinks
{
    public const PRODUCT_BASE = '/product';
    public const PRODUCT_CATEGORY_BASE = 'product-category';
    public const PRODUCT_TAG_BASE = 'product-tags';

    public function __construct()
    {
        add_action('init', [$this, 'setDefaultPermalinks'], 11);
    }

    public function setDefaultPermalinks(): void
    {
        $permalinks = get_option('woocommerce_permalinks') ?: [];
        $wasSet = false;

        if (!isset($permalinks['category_base']) || is_bool($permalinks['category_base'])) {
            $permalinks['category_base'] = self::PRODUCT_CATEGORY_BASE;
            $wasSet = true;
        }

        if (!isset($permalinks['tag_base']) || is_bool($permalinks['tag_base'])) {
            $permalinks['tag_base'] = self::PRODUCT_TAG_BASE;
            $wasSet = true;
        }

        if (!isset($permalinks['product_base']) || is_bool($permalinks['product_base'])) {
            $permalinks['product_base'] = self::PRODUCT_BASE;
            $wasSet = true;
        }

        if ($wasSet) {
            update_option('woocommerce_permalinks', $permalinks);
        }
    }
}
