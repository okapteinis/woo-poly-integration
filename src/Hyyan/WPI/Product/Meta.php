<?php

declare(strict_types=1);

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Admin\MetasList;
use Hyyan\WPI\Taxonomies\Attributes;
use WC_Product;
use PLL_Language;
use WP_Post;
use WP_Term;

class Meta
{
    public function __construct()
    {
        add_action('current_screen', [$this, 'handleProductScreen']);
        add_action('woocommerce_product_quick_edit_save', [$this, 'saveQuickEdit'], 10, 1);
        add_action('save_post_product', [$this, 'handleNewProduct'], 5, 3);
        add_filter('wc_product_has_unique_sku', [$this, 'suppressInvalidDuplicatedSKUErrorMsg'], 100, 3);

        if ('on' === Settings::getOption('importsync', Features::getID(), 'on')) {
            add_action('woocommerce_product_import_inserted_product_object', [$this, 'onImport'], 10, 2);
        }

        if ('on' === Settings::getOption('attributes', Features::getID(), 'on')) {
            add_action('woocommerce_attribute_added', [$this, 'newProductAttribute'], 10, 2);
        }
    }

    public static function getProductTranslationsArrayByID(int $ID, bool $excludeDefault = false): array
    {
        global $polylang;
        $IDS = $polylang->model->post->get_translations($ID);

        if ($excludeDefault) {
            unset($IDS[pll_default_language()]);
        }

        return $IDS;
    }

    public static function getProductTranslationsArrayByObject(WC_Product $product, bool $excludeDefault = false): array
    {
        return static::getProductTranslationsArrayByID($product->get_id(), $excludeDefault);
    }

    public static function getProductTranslationByID(int $ID, string $slug = ''): ?WC_Product
    {
        $product = wc_get_product($ID);
        return $product ? static::getProductTranslationByObject($product, $slug) : null;
    }

    public static function getProductTranslationByObject(WC_Product $product, string $slug = ''): ?WC_Product
    {
        $productTranslationID = pll_get_post($product->get_id(), $slug);
        if ($productTranslationID) {
            $translated = wc_get_product($productTranslationID);
            return $translated ?: $product;
        }
        return $product;
    }

    public static function getLanguageEntity(string $slug): ?PLL_Language
    {
        global $polylang;
        foreach ($polylang->model->get_languages_list() as $lang) {
            if ($lang->slug === $slug) {
                return $lang;
            }
        }
        return null;
    }

    public static function getCurrentUrl(): string
    {
        return sprintf(
            '%s://%s%s',
            is_ssl() ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']
        );
    }

    public static function jsScriptWrapper(
        string $ID,
        string $code,
        bool $jquery = true,
        bool $return = false
    ): ?string {
        $prefix = 'hyyan-wpi-';
        $header = sprintf('/* %s */', $prefix . $ID);
        
        $script = sprintf(
            '%s%s%s',
            $header,
            PHP_EOL,
            $jquery ? sprintf('jQuery(function($){%s});', $code) : $code
        );

        if ($return) {
            return $script;
        }

        printf('<script type="text/javascript">%s</script>', $script);
        return null;
    }

    public function handleNewProduct(int $post_id, WP_Post $post, bool $update): void
    {
        if (!$update && isset($_GET['from_post'])) {
            $this->syncTaxonomiesAndProductAttributes($post_id, $post, $update);
        }
    }

    public function newProductAttribute(int $insert_id, array $attribute): void
    {
        $options = get_option('polylang');
        $sync = $options['taxonomies'];
        $attrname = 'pa_' . $attribute['attribute_name'];
        
        if (!in_array($attribute, $sync)) {
            $options['taxonomies'][] = $attrname;
            update_option('polylang', $options);
        }
    }

    public function onImport(WC_Product $object, array $data): void
    {
        $ProductID = $object->get_id();
        if ($ProductID) {
            do_action('pll_save_post', $ProductID, $object, PLL()->model->post->get_translations($ProductID));
            $this->syncTaxonomiesAndProductAttributes($ProductID, $object, true);
        }
    }

    public static function wpi_filter_pll_metas(array $keys, bool $sync, int $from, int $to, string $lang): array
    {
        static $wpi_keys;
        if (!$wpi_keys) {
            $wpi_keys = static::getProductMetaToCopy($keys);
            $wpi_keys = array_diff($wpi_keys, ['_default_attributes']);
        }
        return $wpi_keys;
    }

    public function saveQuickEdit(WC_Product $product): void
    {
        $this->syncTaxonomiesAndProductAttributes($product->get_id(), $product, true);
    }

    public function syncProductMeta(int $from, int $to, string $lang, bool $sync = true): void
    {
        PLL()->sync->post_metas->copy($from, $to, $lang, $sync);
    }

    public function handleProductScreen(): bool
    {
        $currentScreen = function_exists('get_current_screen') ? get_current_screen() : false;
        if ($currentScreen && $currentScreen->post_type !== 'product') {
            return false;
        }

        add_action('woocommerce_after_product_object_save', [$this, 'syncTaxonomiesAndProductAttributesAfterProductUpdate'], 5, 2);
        
        $ID = false;
        $disable = false;

        if (isset($_GET['post'])) {
            $ID = absint($_GET['post']);
            $disable = $ID && (pll_get_post_language($ID) != pll_default_language())
                && pll_get_post($ID, pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = isset($_GET['new_lang']) && (esc_attr($_GET['new_lang']) != pll_default_language());
            $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
        }

        if ($ID) {
            $this->addProductTypeMeta($ID);
        }

        if ($disable) {
            add_action('admin_print_scripts', [$this, 'addFieldsLocker'], 100);
        }

        return true;
    }

    public function syncTaxonomiesAndProductAttributesAfterProductUpdate(WC_Product $product, $data_store): void
    {
        remove_action('woocommerce_after_product_object_save', [$this, 'syncTaxonomiesAndProductAttributesAfterProductUpdate'], 5, 2);
        $this->syncTaxonomiesAndProductAttributes($product->get_id(), $product, true);
    }

    public function syncTaxonomiesAndProductAttributes(int $post_id, $post, bool $update): void
    {
        add_filter('pll_copy_post_metas', [__CLASS__, 'wpi_filter_pll_metas'], 10, 5);
        
        $taxonomies = get_object_taxonomies(get_post_type($post_id));
        $copy = isset($_GET['new_lang']) && isset($_GET['from_post']);

        if ($copy) {
            $source_id = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
            if ($source_id) {
                $new_lang = $_GET['new_lang'];
                if (!pll_get_post_language($post_id)) {
                    pll_set_post_language($post_id, $new_lang);
                }
                
                $this->syncProductMeta($source_id, $post_id, $new_lang, false);
                $this->copyTerms($source_id, $post_id, $new_lang, $taxonomies);
                $this->syncCustomProductAttributes($source_id, $new_lang);
            }
        } else {
            $langs = pll_languages_list();
            foreach ($langs as $lang) {
                $translation_id = pll_get_post($post_id, $lang);
                if (($translation_id) && ($translation_id != $post_id)) {
                    $this->syncProductMeta($post_id, $translation_id, $lang, true);
                    $this->copyTerms($post_id, $translation_id, $lang, $taxonomies);
                    $this->syncCustomProductAttributes($post_id, $copy);
                    Utilities::flushCacheUpdateLookupTable($translation_id);
                }
            }
        }
    }

    public function syncCustomProductAttributes($source, bool $copy): bool
    {
        if (!$copy) {
            $metas = static::getProductMetaToCopy();
            if (!in_array('_custom_product_attributes', $metas)) {
                return false;
            }
        }

        $product = wc_get_product($source);
        $productattrs = $product->get_attributes();
        $copyattrs = [];

        foreach ($productattrs as $productattr) {
            if (isset($productattr['is_taxonomy']) && $productattr['is_taxonomy'] == 0) {
                $copyattrs[] = $productattr;
            }
        }

        if (count($copyattrs) > 0) {
            if ($copy) {
                $product_obj = Utilities::getProductTranslationByID($product, $copy);
                if ($product_obj) {
                    $product_obj->set_attributes($copyattrs);
                    return true;
                }
            } else {
                $product_translations = Utilities::getProductTranslationsArrayByObject($product);
                foreach ($product_translations as $product_translation) {
                    if ($product_translation != $source) {
                        $product_obj = Utilities::getProductTranslationByID($product_translation);
                        if ($product_obj) {
                            $product_obj->set_attributes($copyattrs);
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function copyTerms(int $old, int $new, string $lang, array $taxonomies): void
    {
        foreach ($taxonomies as $tax) {
            $old_terms = wp_get_object_terms($old, $tax);
            $new_terms = [];

            foreach ($old_terms as $t) {
                $slug = $t->slug;

                switch ($tax) {
                    case "language":
                    case "term_language":
                    case "term_translations":
                    case "post_translations":
                        break;

                    case "product_shipping_class":
                        if (!in_array('product_shipping_class', static::getProductMetaToCopy())) {
                            break;
                        }
                        $new_terms[] = $slug;
                        break;

                    case "product_visibility":
                        if (!in_array('_visibility', static::getProductMetaToCopy())) {
                            break;
                        }
                        // fallthrough
                    case "product_type":
                        $new_terms[] = $slug;
                        break;

                    case "product_tag":
                    case "product_cat":
                    default:
                        if (pll_is_translated_taxonomy($tax)) {
                            $translated_term = pll_get_term($t->term_id, $lang);
                            if ($translated_term) {
                                $new_terms[] = get_term_by('id', $translated_term, $tax)->term_id;
                            } else {
                                $result = static::createDefaultTermTranslation($tax, $t, $slug, $lang, true);
                                if ($result) {
                                    $new_terms[] = $result;
                                }
                            }
                        } else {
                            $new_terms[] = $t->term_id;
                        }
                        break;
                }
            }

            if (count($new_terms) > 0) {
                wp_set_object_terms($new, $new_terms, $tax);
            } else {
                if (count($old_terms) == 0) {
                    wp_set_object_terms($new, false, $tax);
                }
            }
        }
    }

    public static function createDefaultTermTranslation(
        string $tax,
        WP_Term $term,
        string $slug,
        string $lang,
        bool $return_id
    ): mixed {
        global $polylang;
        
        $newterm_name = $term->name;
        $newterm_slug = sanitize_title($slug . '-' . $lang);
        $args = ['slug' => $newterm_slug];

        if ($term->parent) {
            $translated_parent = pll_get_term($term->parent, $lang);
            if ($translated_parent) {
                $args['parent'] = $translated_parent;
            } else {
                $parent_term = \WP_Term::get_instance($term->parent);
                $result = static::createDefaultTermTranslation($tax, $parent_term, $parent_term->slug, $lang, true);
                if ($result) {
                    $args['parent'] = $result;
                }
            }
        }

        $newterm = wp_insert_term($newterm_name, $tax, $args);
        if (is_wp_error($newterm)) {
            error_log($newterm->get_error_message());
            return false;
        }

        $newterm_id = (int) $newterm['term_id'];
        $translations = $polylang->model->term->get_translations($term->term_id);
        $translations[$lang] = $newterm_id;
        $polylang->model->term->set_language($newterm_id, $lang);
        $polylang->model->term->save_translations($term->term_id, $translations);

        return $return_id ? $newterm_id : $newterm_slug;
    }

    public function addProductTypeMeta(int $ID): void
    {
        if ($ID) {
            $meta = get_post_meta($ID, '_translation_porduct_type');
            if (empty($meta)) {
                $product = wc_get_product($ID);
                if ($product) {
                    update_post_meta($ID, '_translation_porduct_type', $product->get_type());
                }
