<?php

namespace Hyyan\WPI;

class Media
{
    public function __construct()
    {
        if (static::isMediaTranslationEnabled()) {
            add_filter(
                'woocommerce_product_get_gallery_image_ids',
                [$this, 'translateGallery']
            );
        }
    }

    public static function isMediaTranslationEnabled(): bool
    {
        $options = get_option('polylang');
        return (bool) ($options['media_support'] ?? false);
    }

    public function translateGallery(array $IDS): array
    {
        $translations = [];
        foreach ($IDS as $ID) {
            $tr = pll_get_post($ID);
            $translations[] = $tr ?: $ID;
        }

        return $translations;
    }
}
