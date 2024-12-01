<?php

namespace Hyyan\WPI\Taxonomies;

class Tags implements TaxonomiesInterface
{
    public static function getNames(): array
    {
        return ['product_tag'];
    }
}
