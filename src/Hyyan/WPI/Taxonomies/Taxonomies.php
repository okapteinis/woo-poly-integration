<?php

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

class Taxonomies
{
    /** @var array<string, object> */
    protected array $managed = [];

    private const SUPPORTED_TAXONOMIES = [
        'attributes' => Attributes::class,
        'categories' => Categories::class,
        'tags' => Tags::class,
    ];

    public function __construct()
    {
        $this->prepareAndGet();

        add_filter('pll_get_taxonomies', [$this, 'getAllTranslateableTaxonomies'], 10, 2);
        add_action('update_option_wpi-features', [__CLASS__, 'updatePolyLangFromWooPolyFeatures'], 10, 3);
        add_action('update_option_wpi-metas-list', [__CLASS__, 'updatePolyLangFromWooPolyMetas'], 10, 3);
    }

    public function getAllTranslateableTaxonomies(array $taxonomies, bool $is_settings): array
    {
        if (!$is_settings) {
            return $taxonomies;
        }

        $add = [];
        foreach (self::SUPPORTED_TAXONOMIES as $tax_type => $class) {
            if ('on' === Settings::getOption($tax_type, Features::getID(), 'on')) {
                $add = array_merge($add, $class::getNames());
            }
        }

        return array_merge($taxonomies, $add);
    }

    public static function updatePolyLangFromWooPolyMetas(mixed $old_value, mixed $new_value, string $option): bool
    {
        return true;
    }

    public static function updatePolyLangFromWooPolyFeatures(array $old_value, array $new_value, string $option): void
    {
        $polylang_options = get_option('polylang');
        $polylang_taxs = $polylang_options['taxonomies'] ?? [];
        $update = false;

        $update = self::handleTaxonomyUpdate(
            $new_value,
            $polylang_taxs,
            $polylang_options,
            'categories',
            'product_cat'
        ) || $update;

        $update = self::handleTaxonomyUpdate(
            $new_value,
            $polylang_taxs,
            $polylang_options,
            'tags',
            'product_tag'
        ) || $update;

        $update = self::handleAttributesUpdate(
            $old_value,
            $new_value,
            $polylang_taxs,
            $polylang_options
        ) || $update;

        if ($update) {
            update_option('polylang', $polylang_options);
        }
    }

    private static function handleTaxonomyUpdate(
        array $new_value,
        array $polylang_taxs,
        array &$polylang_options,
        string $setting_key,
        string $taxonomy_key
    ): bool {
        $update = false;

        if (($new_value[$setting_key] ?? '') === 'on') {
            if (!in_array($taxonomy_key, $polylang_taxs, true)) {
                $polylang_options['taxonomies'][] = $taxonomy_key;
                $update = true;
            }
        } elseif (($key = array_search($taxonomy_key, $polylang_taxs, true)) !== false) {
            unset($polylang_options['taxonomies'][$key]);
            $update = true;
        }

        return $update;
    }

    private static function handleAttributesUpdate(
        array $old_value,
        array $new_value,
        array $polylang_taxs,
        array &$polylang_options
    ): bool {
        if (!isset($old_value['attributes'], $new_value['attributes']) || 
            $old_value['attributes'] === $new_value['attributes']
        ) {
            return false;
        }

        if ($new_value['attributes'] !== 'on') {
            foreach (Attributes::getNames() as $tax) {
                if (in_array($tax, $polylang_taxs, true)) {
                    $polylang_options['taxonomies'] = array_values(
                        array_diff($polylang_options['taxonomies'], [$tax])
                    );
                }
            }
            return true;
        }

        return false;
    }

    protected function prepareAndGet(): array
    {
        $add = [];
        $remove = [];

        foreach (self::SUPPORTED_TAXONOMIES as $option => $class) {
            $names = $class::getNames();
            if ('on' === Settings::getOption($option, Features::getID(), 'on')) {
                $add = array_merge($add, $names);
                if (!isset($this->managed[$class])) {
                    $this->managed[$class] = new $class();
                }
            } else {
                $remove = array_merge($remove, $names);
            }
        }

        return [$add, $remove];
    }
}
