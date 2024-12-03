<?php

declare(strict_types=1);

namespace Hyyan\WPI\Taxonomies;

class ShippingClass implements TaxonomiesInterface
{
    public static function getNames(): array
    {
        return ['product_shipping_class'];
    }
}
