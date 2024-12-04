<?php

declare(strict_types=1);

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;

class GatewayCheque extends \WC_Gateway_Cheque
{
    public function thankyou_page(): void
    {
        if ($this->instructions) {
            echo wpautop(wptexturize(wp_kses_post(
                function_exists('pll__') ? 
                pll__($this->instructions) : 
                __($this->instructions, 'woocommerce')
            )));
        }
    }

    public function email_instructions(\WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if (!$sent_to_admin && 'cheque' === Utilities::get_payment_method($order) && $order->has_status('on-hold')) {
            if ($this->instructions) {
                echo wpautop(wptexturize(
                    function_exists('pll__') ? 
                    pll__($this->instructions) : 
                    __($this->instructions, 'woocommerce')
                )) . PHP_EOL;
            }
        }
    }
}
