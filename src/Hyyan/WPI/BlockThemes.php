<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 * (c) 2025 Ojārs Kapteinis <ojars@kapteinis.lv>
 *
 * This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0
 * International License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-nc-nd/4.0/ or send a letter to Creative Commons,
 * PO Box 1866, Mountain View, CA 94042, USA.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Utilities;

/**
 * BlockThemes.
 *
 * Handle Site Editor and Block Theme support for multilingual stores
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class BlockThemes
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        // Only load if block themes are supported (WordPress 5.9+)
        if (!$this->is_block_theme_supported()) {
            return;
        }

        // Filter block templates by language
        add_filter('get_block_templates', array($this, 'filterTemplatesByLanguage'), 10, 3);

        // Filter template parts by language
        add_filter('get_block_file_template', array($this, 'filterTemplateByLanguage'), 10, 3);

        // Add language context to template rendering
        add_filter('render_block_context', array($this, 'addLanguageToBlockContext'), 10, 2);

        // Filter navigation blocks by language
        add_filter('render_block_core/navigation', array($this, 'filterNavigationBlock'), 10, 2);

        // Register WooCommerce templates for translation
        add_action('init', array($this, 'registerTemplatesForTranslation'));
    }

    /**
     * Check if block themes are supported.
     *
     * @return bool
     */
    protected function is_block_theme_supported()
    {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }

    /**
     * Filter block templates by language.
     *
     * @param array  $query_result Array of found block templates
     * @param array  $query        Arguments to retrieve templates
     * @param string $template_type wp_template or wp_template_part
     *
     * @return array Filtered templates
     */
    public function filterTemplatesByLanguage($query_result, $query, $template_type)
    {
        if (empty($query_result)) {
            return $query_result;
        }

        $current_lang = pll_current_language();

        if (!$current_lang) {
            return $query_result;
        }

        // Filter templates by language
        $filtered = array();

        foreach ($query_result as $template) {
            // Check if template has language assigned
            if ($template->wp_id) {
                $template_lang = pll_get_post_language($template->wp_id);

                // Include templates in current language or without language
                if (!$template_lang || $template_lang === $current_lang) {
                    $filtered[] = $template;
                }
            } else {
                // Include file-based templates
                $filtered[] = $template;
            }
        }

        return $filtered;
    }

    /**
     * Filter individual template by language.
     *
     * @param \WP_Block_Template $block_template The block template object
     * @param string             $id             Template unique identifier
     * @param string             $template_type  wp_template or wp_template_part
     *
     * @return \WP_Block_Template Filtered template
     */
    public function filterTemplateByLanguage($block_template, $id, $template_type)
    {
        if (!$block_template) {
            return $block_template;
        }

        $current_lang = pll_current_language();

        if (!$current_lang || !$block_template->wp_id) {
            return $block_template;
        }

        $template_lang = pll_get_post_language($block_template->wp_id);

        // Try to get translation if template is in different language
        if ($template_lang && $template_lang !== $current_lang) {
            $translated_id = pll_get_post($block_template->wp_id, $current_lang);

            if ($translated_id) {
                $translated_template = get_post($translated_id);

                if ($translated_template) {
                    $block_template->content = $translated_template->post_content;
                    $block_template->wp_id = $translated_id;
                }
            }
        }

        return $block_template;
    }

    /**
     * Add language context to block rendering.
     *
     * @param array $context Block context
     * @param array $block   Block data
     *
     * @return array Modified context
     */
    public function addLanguageToBlockContext($context, $block)
    {
        $current_lang = pll_current_language();

        if ($current_lang) {
            $context['language'] = $current_lang;

            // Add language object for more details
            $lang_obj = Utilities::getLanguageEntity($current_lang);
            if ($lang_obj) {
                $context['languageObject'] = array(
                    'slug'   => $lang_obj->slug,
                    'name'   => $lang_obj->name,
                    'locale' => $lang_obj->locale,
                );
            }
        }

        return $context;
    }

    /**
     * Filter navigation block by language.
     *
     * @param string $block_content Block content
     * @param array  $block         Block data
     *
     * @return string Filtered block content
     */
    public function filterNavigationBlock($block_content, $block)
    {
        // Navigation menus are already handled by Polylang
        // This ensures compatibility with block-based navigation
        return $block_content;
    }

    /**
     * Register WooCommerce templates for translation.
     *
     * This allows custom WooCommerce templates created in Site Editor
     * to be translated via Polylang.
     */
    public function registerTemplatesForTranslation()
    {
        // Register wp_template post type with Polylang
        $options = get_option('polylang');
        $post_types = isset($options['post_types']) ? $options['post_types'] : array();

        if (!in_array('wp_template', $post_types, true)) {
            $post_types[] = 'wp_template';
            $options['post_types'] = $post_types;
            update_option('polylang', $options);
        }

        // Register wp_template_part post type with Polylang
        if (!in_array('wp_template_part', $post_types, true)) {
            $post_types[] = 'wp_template_part';
            $options['post_types'] = $post_types;
            update_option('polylang', $options);
        }

        // Add filter to register template post types with Polylang
        add_filter('pll_get_post_types', array($this, 'registerTemplatePostTypes'), 10, 2);
    }

    /**
     * Register template post types with Polylang.
     *
     * @param array $post_types Array of post type names
     * @param bool  $is_settings Whether the call is from settings page
     *
     * @return array Modified post types array
     */
    public function registerTemplatePostTypes($post_types, $is_settings = false)
    {
        if (!in_array('wp_template', $post_types, true)) {
            $post_types[] = 'wp_template';
        }

        if (!in_array('wp_template_part', $post_types, true)) {
            $post_types[] = 'wp_template_part';
        }

        return $post_types;
    }
}
