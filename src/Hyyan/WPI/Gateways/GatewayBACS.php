<?php

declare(strict_types=1);

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;

class GatewayBACS extends \WC_Gateway_BACS
{
    public function thankyou_page(int $order_id): void
    {
        if ($this->instructions) {
            echo wpautop(wptexturize(wp_kses_post(
                function_exists('pll__') ? 
                pll__($this->instructions) : 
                __($this->instructions, 'woocommerce')
            )));
        }
        $this->bank_details($order_id);
    }

    public function email_instructions(\WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if (!$sent_to_admin && 'bacs' === Utilities::get_payment_method($order) && $order->has_status('on-hold')) {
            if ($this->instructions) {
                echo wpautop(wptexturize(
                    function_exists('pll__') ? 
                    pll__($this->instructions) : 
                    __($this->instructions, 'woocommerce')
                )) . PHP_EOL;
            }
            $this->bank_details(Utilities::get_orderid($order));
        }
    }

    private function bank_details(int $order_id = 0): void
    {
        if (empty($this->account_details)) {
            return;
        }

        $order = wc_get_order($order_id);
        $country = $order->get_billing_country();
        $locale = $this->get_country_locale();
        $sortcode = isset($locale[$country]['sortcode']['label']) ? 
            $locale[$country]['sortcode']['label'] : 
            __('Sort code', 'woocommerce');

        $bacs_accounts = apply_filters('woocommerce_bacs_accounts', $this->account_details);

        if (!empty($bacs_accounts)) {
            $account_html = '';
            $has_details = false;

            foreach ($bacs_accounts as $bacs_account) {
                $bacs_account = (object) $bacs_account;
                if ($bacs_account->account_name) {
                    $account_html .= '<h3>' . wp_kses_post(wp_unslash($bacs_account->account_name)) . '</h3>' . PHP_EOL;
                    $account_html .= '<ul class="order_details bacs_details">' . PHP_EOL;

                    $account_fields = apply_filters('woocommerce_bacs_account_fields', [
                        'bank_name' => [
                            'label' => __('Bank', 'woocommerce'),
                            'value' => $bacs_account->bank_name,
                        ],
                        'account_number' => [
                            'label' => __('Account number', 'woocommerce'),
                            'value' => $bacs_account->account_number,
                        ],
                        'sort_code' => [
                            'label' => $sortcode,
                            'value' => $bacs_account->sort_code,
                        ],
                        'iban' => [
                            'label' => __('IBAN', 'woocommerce'),
                            'value' => $bacs_account->iban,
                        ],
                        'bic' => [
                            'label' => __('BIC', 'woocommerce'),
                            'value' => $bacs_account->bic,
                        ],
                    ], $order_id);

                    foreach ($account_fields as $field_key => $field) {
                        if (!empty($field['value'])) {
                            $account_html .= '<li class="' . esc_attr($field_key) . '">' . 
                                wp_kses_post($field['label']) . ': <strong>' . 
                                wp_kses_post(wptexturize($field['value'])) . '</strong></li>' . PHP_EOL;
                            $has_details = true;
                        }
                    }

                    $account_html .= '</ul>';
                }
            }

            if ($has_details) {
                echo '<h2 class="wc-bacs-bank-details-heading">' . __('Our bank details', 'woocommerce') . 
                    '</h2>' . PHP_EOL . $account_html;
            }
        }
    }
}
