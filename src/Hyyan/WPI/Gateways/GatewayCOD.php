<?php

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;
use WC_Gateway_COD;
use WC_Order;

class GatewayCOD extends WC_Gateway_COD
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

    public function email_instructions(WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if (!$this->instructions || 
            $sent_to_admin || 
            'cod' !== Utilities::get_payment_method($order)
        ) {
            return;
        }

        echo wpautop(wptexturize(
            function_exists('pll__') ? 
                pll__($this->instructions) : 
                __($this->instructions, 'woocommerce')
        )) . PHP_EOL;
    }
}
