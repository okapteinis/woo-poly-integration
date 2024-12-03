<?php

declare(strict_types=1);

namespace Hyyan\WPI;

class Privacy
{
    public function __construct()
    {
        $this->registerPrivacyStrings();
        add_filter('woocommerce_get_privacy_policy_text', [$this, 'translatePrivacyPolicyText'], 10, 2);
        add_filter('woocommerce_demo_store', [$this, 'translateDemoStoreNotice'], 10, 2);
        add_filter('woocommerce_get_terms_and_conditions_checkbox_text', [$this, 'translateText'], 10, 1);
    }

    public function registerPrivacyStrings(): void
    {
        $this->registerString(
            'woocommerce_checkout_privacy_policy_text',
            get_option(
                'woocommerce_checkout_privacy_policy_text',
                sprintf(
                    __('Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.', 'woocommerce'),
                    '[privacy_policy]'
                )
            )
        );

        $this->registerString(
            'woocommerce_registration_privacy_policy_text',
            get_option(
                'woocommerce_registration_privacy_policy_text',
                sprintf(
                    __('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.', 'woocommerce'),
                    '[privacy_policy]'
                )
            )
        );

        $this->registerString(
            'woocommerce_store_notice',
            get_option('woocommerce_demo_store_notice')
        );

        $this->registerString(
            'woocommerce_checkout_terms_and_conditions_checkbox_text',
            get_option('woocommerce_checkout_terms_and_conditions_checkbox_text')
        );
    }

    private function registerString(string $setting, ?string $value): void
    {
        if (function_exists('pll_register_string') && $value !== null) {
            pll_register_string(
                $setting,
                $value,
                __('WooCommerce Privacy', 'woo-poly-integration'),
                true
            );
        }
    }

    public function translatePrivacyPolicyText(string $text, string $type): string
    {
        return $this->translateText($text);
    }

    public function translateText(string $text): string
    {
        if (!function_exists('pll_register_string')) {
            return $text;
        }
        return pll__($text) ?: $text;
    }

    public function translateDemoStoreNotice(string $html, string $notice): string
    {
        if (!function_exists('pll_register_string')) {
            return $html;
        }

        $trans = pll__($notice);
        return sprintf(
            '<p class="woocommerce-store-notice demo_store">%s <a href="#" class="woocommerce-store-notice__dismiss-link">%s</a></p>',
            wp_kses_post($trans),
            esc_html__('Dismiss', 'woocommerce')
        );
    }
}
