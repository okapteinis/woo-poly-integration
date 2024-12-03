<?php

declare(strict_types=1);

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;
use WC_Order;

class GatewayCOD
{
    public function __construct()
    {
        add_action('woocommerce_thankyou_cod', [$this, 'thankyou_page']);
        add_action('woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3);
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
    }

    public function email_instructions(WC_Order $order, bool $sent_to_admin, bool $plain_text = false): void
    {
        if (!$this->instructions ||
            $sent_to_admin ||
            'cod' !== Utilities::get_payment_method($order)
        ) {
            return;
        }

        echo wpautop(
            wptexturize(
                function_exists('pll__') 
                    ? pll__($this->instructions) 
                    : __($this->instructions, 'woocommerce')
            )
        ) . PHP_EOL;
    }
}
