<?php

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Utilities;
use WC_Product_Variable;
use WC_Product;
use WC_Product_Variation;
use WP_Post;
use WP_Term;

class Variation
{
    public const DUPLICATE_KEY = '_point_to_variation';

    protected WC_Product_Variable $from;
    protected WC_Product $to;

    public function __construct(WC_Product_Variable $from, WC_Product $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function duplicate(): bool
    {
        $fromVariation = $this->from->get_children();
        if (empty($fromVariation)) {
            return false;
        }

        if ($this->to->get_id() === $this->from->get_id()) {
            $this->handleSameProductVariations($fromVariation);
        } else {
            $this->handleDifferentProductVariations($fromVariation);
        }

        return true;
    }

    private function handleSameProductVariations(array $variations): void
    {
        foreach ($variations as $variation_id) {
            $variation = $this->from->get_available_variation($variation_id);
            if (!metadata_exists('post', $variation['variation_id'], self::DUPLICATE_KEY)) {
                update_post_meta(
                    $variation['variation_id'],
                    self::DUPLICATE_KEY,
                    $variation['variation_id']
                );
            }
        }
    }

    private function handleDifferentProductVariations(array $variations): void
    {
        $previousVariations = $this->to->get_children();
        set_time_limit(0);

        foreach ($variations as $variation_id) {
            $variation = $this->from->get_available_variation($variation_id);
            $posts = $this->getVariationPosts($variation_id);
            
            $this->processVariationPosts($posts, $variation, $variation_id, $previousVariations);
        }

        $this->cleanupPreviousVariations($previousVariations);
        $this->updateProductStatus();

        set_time_limit(ini_get('max_execution_time'));
    }

    private function getVariationPosts(int $variation_id): array
    {
        return get_posts([
            'meta_key' => self::DUPLICATE_KEY,
            'meta_value' => $variation_id,
            'post_type' => 'product_variation',
            'post_status' => 'any',
            'post_parent' => $this->to->get_id(),
            'lang' => '',
        ]);
    }

    private function processVariationPosts(
        array $posts,
        array $variation,
        int $variation_id,
        array &$previousVariations
    ): void {
        switch (count($posts)) {
            case 1:
                $this->update(wc_get_product($variation_id), $posts[0], $variation);
                unset($previousVariations[array_search($posts[0]->ID, $previousVariations)]);
                break;
            case 0:
                $this->insert(wc_get_product($variation_id), $variation);
                break;
            default:
                $this->handleDuplicateVariations($posts, $variation, $variation_id, $previousVariations);
                break;
        }
    }

    private function handleDuplicateVariations(
        array $posts,
        array $variation,
        int $variation_id,
        array &$previousVariations
    ): void {
        $this->update(wc_get_product($variation_id), $posts[0], $variation);
        
        for ($i = 1; $i < count($posts); $i++) {
            if ($duplicate = wc_get_product($posts[$i])) {
                $duplicate->delete(true);
                unset($previousVariations[array_search($posts[$i]->ID, $previousVariations)]);
            }
        }
    }

    private function cleanupPreviousVariations(array $previousVariations): void
    {
        foreach ($previousVariations as $variation_id) {
            if ($removedVariation = wc_get_product($variation_id)) {
                $removedVariation->delete(true);
            }
        }
    }

    private function updateProductStatus(): void
    {
        $target_status = $this->from->get_stock_status();
        wc_update_product_stock_status($this->to->get_id(), $target_status);
        Utilities::flushCacheUpdateLookupTable($this->to->get_id());
    }

    // ... [pārējās metodes ar līdzīgām izmaiņām]
}
