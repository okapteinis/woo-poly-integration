<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use WC_Coupon;

class Coupon
{
    public function __construct()
    {
        add_filter('woocommerce_coupon_get_description', [$this, 'translateDescription'], 10, 2);
        add_filter('woocommerce_coupon_message', [$this, 'translateMessage'], 10, 2);
        add_filter('woocommerce_coupon_error', [$this, 'translateMessage'], 10, 2);
        add_action('woocommerce_coupon_loaded', [$this, 'couponLoaded']);
    }

    public function translateDescription(string $value, WC_Coupon $coupon): string
    {
        $this->registerCouponStringsForTranslation();
        return pll__($value);
    }

    public function translateMessage(string $value, WC_Coupon $coupon): string
    {
        $this->registerCouponStringsForTranslation();
        $code = $coupon->get_code();
        return $code ? sprintf(esc_html__('Coupon: %s', 'woocommerce'), pll__($code)) : $value;
    }

    public function couponLoaded(WC_Coupon $coupon): WC_Coupon
    {
        $productIDS = [];
        $excludeProductIDS = [];
        $productCategoriesIDS = [];
        $excludeProductCategoriesIDS = [];

        foreach ($coupon->get_product_ids() as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $productIDS[] = $_id;
            }
        }

        foreach ($coupon->get_excluded_product_ids() as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $excludeProductIDS[] = $_id;
            }
        }

        foreach ($coupon->get_product_categories() as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $productCategoriesIDS[] = $_id;
            }
        }

        foreach ($coupon->get_excluded_product_categories() as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $excludeProductCategoriesIDS[] = $_id;
            }
        }

        $coupon->set_product_ids($productIDS);
        $coupon->set_excluded_product_ids($excludeProductIDS);
        $coupon->set_product_categories($productCategoriesIDS);
        $coupon->set_excluded_product_categories($excludeProductCategoriesIDS);

        return $coupon;
    }

    protected function getProductPostTranslationIDS(int $ID): array
    {
        $result = [$ID];
        $product = wc_get_product($ID);
        
        if ($product && $product->get_type() === 'variation') {
            $IDS = Product\Variation::getRelatedVariation($ID, true);
            if (is_array($IDS)) {
                $result = array_merge($result, $IDS);
            }
        } else {
            $IDS = Utilities::getProductTranslationsArrayByID($ID);
            if (is_array($IDS)) {
                $result = array_merge($result, $IDS);
            }
        }

        return $result;
    }

    protected function getProductTermTranslationIDS(int $ID): array
    {
        $IDS = Utilities::getTermTranslationsArrayByID($ID);
        return $IDS ?: [$ID];
    }

    protected function registerCouponStringsForTranslation(): void
    {
        // Implementation not shown in search results
    }
}
