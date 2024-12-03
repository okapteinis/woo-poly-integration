<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use WC_Product;
use WC_Product_Variation;

class Duplicator
{
    public function __construct()
    {
        add_action(
            'woocommerce_product_duplicate',
            [$this, 'unlinkOrginalProductTranslations'],
            10,
            3
        );

        add_action(
            'woocommerce_product_duplicate_before_save',
            [$this, 'unlinkCopiedVariations'],
            10,
            3
        );
    }

    public function unlinkCopiedVariations(
        WC_Product $child_duplicate,
        WC_Product $child
    ): void {
        if (!$child_duplicate instanceof WC_Product_Variation) {
            return;
        }

        $child_duplicate->delete_meta_data(Variation::DUPLICATE_KEY);
    }

    public function unlinkOrginalProductTranslations(
        WC_Product $duplicate,
        WC_Product $product
    ): void {
        global $polylang;
        $polylang->model->post->delete_translation($duplicate->get_id());
    }
}
