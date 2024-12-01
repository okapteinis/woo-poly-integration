<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;
use WC_Coupon;

class Coupon
{
    private static bool $enable_logging = false;
    private static bool $enable_wjecf = false;

    public function __construct()
    {
        if (defined('DOING_CRON')) {
            return;
        }

        if ('on' === Settings::getOption('coupons', Features::getID(), 'on')) {
            add_action('woocommerce_coupon_loaded', [$this, 'couponLoaded']);
            add_action('wp_loaded', [$this, 'adminRegisterCouponStrings']);
            add_filter('woocommerce_cart_totals_coupon_label', [$this, 'translateLabel'], 20, 2);
            add_filter('woocommerce_coupon_get_description', [$this, 'translateDescription'], 10, 2);

            $enable_wjecf = function_exists('WJECF');
            if ($enable_wjecf) {
                if (self::$enable_logging) {
                    error_log('woopoly enabled wjecf translation: ' . ' in request: ' . ($_SERVER['REQUEST_URI'] ?? ' no $_SERVER[REQUEST_URI] available'));
                }
                add_filter('woocommerce_coupon_get__wjecf_enqueue_message', [$this, 'translateMessage'], 10, 2);
                add_filter('woocommerce_coupon_get__wjecf_select_free_product_message', [$this, 'translateMessage'], 10, 2);
                add_filter('woocommerce_coupon_get__wjecf_free_product_ids', [$this, 'getFreeProductsInLanguage'], 10, 2);
            }
        }
    }

    public function getFreeProductsInLanguage(string $productIds, WC_Coupon $coupon): array
    {
        if (is_admin()) {
            return $productIds;
        }

        $productLang = pll_current_language();
        $mappedIds = [];
        
        foreach (explode(',', $productIds) as $productId) {
            $mappedIds[] = Utilities::get_translated_variation($productId, $productLang);
        }
        
        return $mappedIds;
    }

    public function translateLabel(string $value, WC_Coupon $coupon): string
    {
        $this->registerCouponStringsForTranslation();
        $code = $coupon->get_code();
        return $code ? sprintf(esc_html__('Coupon: %s', 'woocommerce'), pll__($code)) : $value;
    }

    public function translateDescription(string $value, WC_Coupon $coupon): string
    {
        $this->registerCouponStringsForTranslation();
        return pll__($value);
    }

    public function translateMessage(string $value, WC_Coupon $coupon): string
    {
        $this->registerCouponStringsForTranslation();
        return pll__($value);
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
        
        return $IDS ?: [$ID];
    }

    protected function getProductTermTranslationIDS(int $ID): array
    {
        $IDS = Utilities::getTermTranslationsArrayByID($ID);
        return $IDS ?: [$ID];
    }
}
