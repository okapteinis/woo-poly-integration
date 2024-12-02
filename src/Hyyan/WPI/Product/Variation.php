<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use WC_Product;
use WC_Product_Variable;
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
            foreach ($fromVariation as $variation_id) {
                $variation = $this->from->get_available_variation($variation_id);
                if (!metadata_exists('post', $variation['variation_id'], self::DUPLICATE_KEY)) {
                    update_post_meta(
                        $variation['variation_id'],
                        self::DUPLICATE_KEY,
                        $variation['variation_id']
                    );
                }
            }
        } else {
            $previousVariations = $this->to->get_children();
            set_time_limit(0);

            foreach ($fromVariation as $variation_id) {
                $variation = $this->from->get_available_variation($variation_id);
                $posts = get_posts([
                    'meta_key' => self::DUPLICATE_KEY,
                    'meta_value' => $variation_id,
                    'post_type' => 'product_variation',
                    'post_status' => 'any',
                    'post_parent' => $this->to->get_id(),
                    'lang' => '',
                ]);

                switch (count($posts)) {
                    case 1:
                        $this->update(
                            wc_get_product($variation_id),
                            $posts[0],
                            $variation
                        );
                        unset($previousVariations[array_search($posts[0]->ID, $previousVariations)]);
                        break;
                    case 0:
                        $this->insert(wc_get_product($variation_id), $variation);
                        break;
                    default:
                        $this->update(
                            wc_get_product($variation_id),
                            $posts[0],
                            $variation
                        );
                        $count = count($posts);
                        error_log("woo-poly is deleting duplicate variations for variation " . $variation['variation_id'] .
                            " in product " . $this->from->get_id() . " translation " . $this->to->get_id());
                        for ($i = 1; $i < $count; $i++) {
                            $duplicate = wc_get_product($posts[$i]);
                            if ($duplicate) {
                                $duplicate->delete(true);
                                unset($previousVariations[array_search($posts[$i]->ID, $previousVariations)]);
                            }
                        }
                        break;
                }
            }

            foreach ($previousVariations as $variation_id) {
                $removedVariation = wc_get_product($variation_id);
                if ($removedVariation) {
                    $removedVariation->delete(true);
                }
            }

            $target_status = $this->from->get_stock_status();
            wc_update_product_stock_status($this->to->get_id(), $target_status);
            Utilities::flushCacheUpdateLookupTable($this->to->get_id());
            set_time_limit(ini_get('max_execution_time'));
        }

        return true;
    }

    public static function getRelatedVariation(int $variationID, bool $returnIDS = false): mixed
    {
        if (!$variationID) {
            return false;
        }

        global $wpdb;
        $postids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d",
            self::DUPLICATE_KEY,
            $variationID
        ));

        if ($returnIDS) {
            return $postids;
        }

        $result = [];
        foreach ($postids as $postid) {
            $product = wc_get_product($postid);
            if ($product) {
                $result[] = $product;
            }
        }

        return $result;
    }

    public static function deleteRelatedVariation(int $variationID): void
    {
        $products = (array) static::getRelatedVariation($variationID);
        foreach ($products as $product) {
            wp_delete_post($product->get_id(), true);
        }
    }

    protected function insert(WC_Product_Variation $variation, array $metas): void
    {
        $this->addDuplicateMeta($variation->get_id());
        
        $data = (array) get_post($variation->get_id());
        $newParentId = $this->to->get_id();
        unset($data['ID']);
        $data['post_parent'] = $newParentId;
        
        $ID = wp_insert_post($data);
        if ($ID) {
            $targetLang = pll_get_post_language($newParentId);
            if (!$targetLang && isset($_GET['new_lang'])) {
                $targetLang = $_GET['new_lang'];
            }

            if ($targetLang) {
                pll_set_post_language($ID, $targetLang);
            }

            update_post_meta($ID, self::DUPLICATE_KEY, $metas['variation_id']);
            $this->copyVariationMetas($variation->get_id(), $ID);
            Utilities::flushCacheUpdateLookupTable($ID);
            Utilities::flushCacheUpdateLookupTable($newParentId);
        }
    }

    protected function update(WC_Product_Variation $variation, WP_Post $post, array $metas): void
    {
        $this->copyVariationMetas($variation->get_id(), $post->ID);
        pll_set_post_language($post->ID, pll_get_post_language($post->post_parent));
        
        if ($post->post_status != $variation->get_status()) {
            $destVariation = wc_get_product($post->ID);
            $destVariation->set_status($variation->get_status());
            $destVariation->save();
        }
        
        Utilities::flushCacheUpdateLookupTable($post->ID);
    }

    protected function addDuplicateMeta(int $ID): void
    {
        if ($ID && empty(get_post_meta($ID, self::DUPLICATE_KEY))) {
            update_post_meta($ID, self::DUPLICATE_KEY, $ID);
        }
    }

    protected function syncShippingClass(int $from, int $to): void
    {
        if (in_array('product_shipping_class', Meta::getProductMetaToCopy())) {
            $variation_from = wc_get_product($from);
            if ($variation_from) {
                $shipping_class = $variation_from->get_shipping_class();
                if ($shipping_class) {
                    $shipping_terms = get_term_by('slug', $shipping_class, 'product_shipping_class');
                    if ($shipping_terms) {
                        wp_set_post_terms($to, [$shipping_terms->term_id], 'product_shipping_class');
                    }
                } else {
                    wp_set_post_terms($to, [], 'product_shipping_class');
                }
            }
        }
    }

    protected function copyVariationMetas(int $from, int $to): bool
    {
        $metas_from = get_post_custom($from);
        $metas_to = get_post_custom($to);
        $keys = array_unique(array_merge(array_keys($metas_from), array_keys($metas_to)));
        $metas_nosync = Meta::getDisabledProductMetaToCopy();

        if (isset($metas_to['_variation_description'])) {
            $metas_nosync[] = '_variation_description';
        }

        foreach ($keys as $key) {
            if (!in_array($key, $metas_nosync)) {
                delete_post_meta($to, $key);
                if (isset($metas_from[$key])) {
                    if (substr($key, 0, 10) == 'attribute_') {
                        $this->handleAttributeMeta($key, $metas_from[$key], $to, $from);
                    } else {
                        foreach ($metas_from[$key] as $value) {
                            add_post_meta($to, $key, maybe_unserialize($value));
                        }
                    }
                }
            }
        }

        $this->syncShippingClass($from, $to);
        do_action(HooksInterface::PRODUCT_VARIATION_COPY_META_ACTION, $from, $to, $this->from, $this->to);

        return true;
    }

    protected function getTermBySlug(string $taxonomy, string $value): ?WP_Term
    {
        $terms = get_terms([
            'get' => 'all',
            'number' => 1,
            'taxonomy' => $taxonomy,
            'update_term_meta_cache' => false,
            'orderby' => 'none',
            'suppress_filter' => true,
            'slug' => $value,
            'lang' => pll_default_language(),
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return null;
        }

        $term = array_shift($terms);
        return get_term($term, $taxonomy);
    }

    protected function handleAttributeMeta(string $key, array $values, int $to, int $from): void
    {
        $translated = [];
        $tax = str_replace('attribute_', '', $key);

        foreach ($values as $termSlug) {
            if (pll_is_translated_taxonomy($tax) && $termSlug) {
                $term = $this->getTermBySlug($tax, $termSlug);
                if ($term) {
                    $term_id = $term->term_id;
                    $lang = isset($_GET['new_lang']) ? 
                        esc_attr($_GET['new_lang']) : 
                        pll_get_post_language($this->to->get_id());
                    
                    $translated_term = pll_get_term($term_id, $lang);
                    if ($translated_term) {
                        $translated[] = get_term_by('id', $translated_term, $tax)->slug;
                    } else {
                        $fromLang = pll_get_post_language($from);
                        if (!$fromLang) {
                            $fromLang = pll_get_post_language(wc_get_product($from)->get_parent_id());
                        }

                        if ($fromLang) {
                            $term = pll_get_term($term_id, $fromLang);
                            if ($term) {
                                $term = get_term_by('id', $term, $tax);
                                $result = Meta::createDefaultTermTranslation($tax, $term, $termSlug, $lang, false);
                                if ($result) {
                                    $translated[] = $result;
                                } else {
                                    $translated[] = $termSlug;
                                }
                            } else {
                                $translated[] = $termSlug;
                            }
                        } else {
                            $translated[] = $termSlug;
                        }
                    }
                } else {
                    $translated[] = $termSlug;
                }
            } else {
                $translated[] = $termSlug;
            }
        }

        foreach ($translated as $value) {
            add_post_meta($to, $key, $value);
        }
    }
}
