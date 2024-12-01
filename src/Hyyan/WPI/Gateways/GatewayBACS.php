<?php

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;
use WC_Gateway_BACS;
use WC_Order;

class GatewayBACS extends WC_Gateway_BACS
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

    public function email_instructions(WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if ($sent_to_admin || 
            'bacs' !== Utilities::get_payment_method($order) || 
            !$order->has_status('on-hold')
        ) {
            return;
        }

        if ($this->instructions) {
            echo wpautop(wptexturize(
                function_exists('pll__') ? 
                    pll__($this->instructions) : 
                    __($this->instructions, 'woocommerce')
            )) . PHP_EOL;
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
                $account_html .= sprintf(
                    '<h3 class="wc-bacs-bank-details-account-name">%s:</h3>%s',
                    wp_kses_post(wp_unslash($bacs_account->account_name)),
                    PHP_EOL
                );
            }

            $account_html .= '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;
            $account_fields = $this->getAccountFields($bacs_account, $sortcode, $order_id);

            foreach ($account_fields as $field_key => $field) {
                if (!empty($field['value'])) {
                    $account_html .= sprintf(
                        '<li class="%s">%s: <strong>%s</strong></li>%s',
                        esc_attr($field_key),
                        wp_kses_post($field['label']),
                        wp_kses_post(wptexturize($field['value'])),
                        PHP_EOL
                    );
                    $has_details = true;
                }
            }

            $account_html .= '</ul>';
        }

        if ($has_details) {
            printf(
                '<section class="woocommerce-bacs-bank-details"><h2 class="wc-bacs-bank-details-heading">%s</h2>%s%s</section>',
                __('Our bank details', 'woocommerce'),
                PHP_EOL,
                $account_html
            );
        }
    }

    private function getAccountFields(object $bacs_account, string $sortcode, int $order_id): array
    {
        return apply_filters('woocommerce_bacs_account_fields', [
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
    }
}
