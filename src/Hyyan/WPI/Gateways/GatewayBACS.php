<?php

declare(strict_types=1);

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;
use WC_Order;

class GatewayBACS
{
    public function __construct()
    {
        add_filter('woocommerce_bacs_account_fields', [$this, 'accountFields'], 10, 2);
    }

    public function thankyou_page(int $order_id): void
    {
        if ($this->instructions) {
            echo wpautop(
                wptexturize(
                    wp_kses_post(
                        function_exists('pll__') 
                            ? pll__($this->instructions) 
                            : __($this->instructions, 'woocommerce')
                    )
                )
            );
        }
        $this->bank_details($order_id);
    }

    public function email_instructions(WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if ($sent_to_admin || 
            'bacs' !== Utilities::get_payment_method($order) || 
            !$order->has_status('on-hold')
        ) {
            return;
        }

        if ($this->instructions) {
            echo wpautop(
                wptexturize(
                    function_exists('pll__') 
                        ? pll__($this->instructions) 
                        : __($this->instructions, 'woocommerce')
                )
            ) . PHP_EOL;
        }
        $this->bank_details(Utilities::get_orderid($order));
    }

    private function bank_details(int $order_id = 0): void
    {
        if (empty($this->account_details)) {
            return;
        }

        $order = wc_get_order($order_id);
        $country = $order->get_billing_country();
        $locale = $this->get_country_locale();
        $sortcode = $locale[$country]['sortcode']['label'] ?? __('Sort code', 'woocommerce');

        $bacs_accounts = apply_filters('woocommerce_bacs_accounts', $this->account_details);
        if (empty($bacs_accounts)) {
            return;
        }

        $account_html = '';
        $has_details = false;

        foreach ($bacs_accounts as $bacs_account) {
            $bacs_account = (object) $bacs_account;
            if ($bacs_account->account_name) {
                // Implementation continues...
            }
        }
    }
}
