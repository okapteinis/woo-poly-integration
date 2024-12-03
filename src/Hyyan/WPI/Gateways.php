<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use WC_Payment_Gateway;
use WC_Payment_Gateways;

class Gateways
{
    protected array $enabledGateways;

    public function __construct()
    {
        $this->enabledGateways = $this->getEnabledPaymentGateways();
        $this->registerGatewayStringsForTranslation();
        $this->loadPaymentGatewaysExtensions();
    }

    public function setPaypalLocalCode(array $args): array
    {
        $args['locale.x'] = pll_current_language('locale');
        return $args;
    }

    public function getEnabledPaymentGateways(): array
    {
        $enabledGateways = [];
        $gateways = WC_Payment_Gateways::instance();

        if (count($gateways->payment_gateways) > 0) {
            foreach ($gateways->payment_gateways() as $gateway) {
                if ($this->isEnabled($gateway)) {
                    $enabledGateways[$gateway->id] = $gateway;
                }
            }
        }

        return $enabledGateways;
    }

    public function isEnabled(WC_Payment_Gateway $gateway): bool
    {
        return $gateway->enabled === 'yes';
    }

    public function loadPaymentGatewaysExtensions(): void
    {
        $this->removeGatewayActions();
        foreach ($this->enabledGateways as $gateway) {
            match($gateway->id) {
                'bacs' => new Gateways\GatewayBACS(),
                'cheque' => new Gateways\GatewayCheque(),
                'cod' => new Gateways\GatewayCOD(),
                default => null
            };

            do_action(
                HooksInterface::GATEWAY_LOAD_EXTENSION . $gateway->id,
                $gateway,
                $this->enabledGateways
            );
        }
    }

    public function removeGatewayActions(): void
    {
        $default_gateways = ['bacs', 'cheque', 'cod'];
        foreach ($this->enabledGateways as $gateway) {
            if (in_array($gateway->id, $default_gateways, true)) {
                remove_action('woocommerce_email_before_order_table', [$gateway, 'email_instructions']);
                remove_action('woocommerce_thankyou_' . $gateway->id, [$gateway, 'thankyou_page']);

                if ($gateway->id === 'bacs') {
                    remove_action(
                        'woocommerce_update_options_payment_gateways_' . $gateway->id,
                        [$gateway, 'save_account_details']
                    );
                }
            }
        }
    }

    public function registerGatewayStringsForTranslation(): void
    {
        if (!function_exists('pll_register_string') || empty($this->enabledGateways)) {
            return;
        }

        foreach ($this->enabledGateways as $gateway) {
            $settings = get_option($gateway->plugin_id . $gateway->id . '_settings');
            if (empty($settings)) {
                continue;
            }
            $this->registerGatewayStrings($gateway, $settings);
        }
    }

    private function registerGatewayStrings(WC_Payment_Gateway $gateway, array $settings): void
    {
        $fields = ['title', 'description', 'instructions'];
        foreach ($fields as $field) {
            if (isset($settings[$field])) {
                pll_register_string(
                    $gateway->plugin_id . $gateway->id . '_gateway_' . $field,
                    $settings[$field],
                    __('WooCommerce Payment Gateways', 'woo-poly-integration')
                );
            }
        }
    }

    public function translatePaymentGatewayTitle(string $title, string $id): string
    {
        return function_exists('pll__') ? pll__($title) : __($title, 'woocommerce');
    }

    public function translatePaymentGatewayDescription(string $description, string $id): string
    {
        return function_exists('pll__') ? pll__($description) : __($description, 'woocommerce');
    }
}
