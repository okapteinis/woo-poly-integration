<?php

declare(strict_types=1);

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

class Categories
{
    public function __construct()
    {
        add_action('create_term', [$this, 'doSyncProductCatCustomFields'], 10, 3);
        add_action('edit_term', [$this, 'doSyncProductCatCustomFields'], 10, 3);
        add_action('admin_print_scripts', [$this, 'copyProductCatCustomFields']);
    }

    public function doSyncProductCatCustomFields(int $termID, int $ttID, string $taxonomy): void
    {
        if ($taxonomy !== 'product_cat') {
            return;
        }

        if (isset($_POST['display_type'])) {
            $this->doSyncProductCatCustomFields(
                $termID,
                'display_type',
                esc_attr($_POST['display_type'])
            );
        }

        if (isset($_POST['product_cat_thumbnail_id'])) {
            $this->doSyncProductCatCustomFields(
                $termID,
                'thumbnail_id',
                absint($_POST['product_cat_thumbnail_id'])
            );
        }

        do_action(
            HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS,
            $this,
            $termID,
            $taxonomy
        );
    }

    public function copyProductCatCustomFields(): bool
    {
        if (!isset($_GET['from_tag'], $_GET['new_lang'])) {
            return false;
        }

        $ID = esc_attr($_GET['from_tag']);
        $type = get_term_meta($ID, 'display_type', true);
        $thumbID = absint(get_term_meta($ID, 'thumbnail_id', true));
        $image = $thumbID ?
            wp_get_attachment_thumb_url($thumbID) :
            wc_placeholder_img_src();

        $this->outputCategoryCustomFieldsScript($type, $image, $thumbID);

        do_action(
            HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS,
            $ID
        );

        return true;
    }

    private function outputCategoryCustomFieldsScript(string $type, string $image, int $thumbID): void
    {
        $code = sprintf(
            'jQuery(function($){
                $("#display_type option[value=\'%s\']").prop("selected", true);
                $("#product_cat_thumbnail img").attr("src", "%s");
                $("#product_cat_thumbnail_id").val("%d");
                $(".remove_image_button").show();
            });',
            $type,
            $image,
            $thumbID
        );

        Utilities::jsScriptWrapper('product-cat-custom-fields', $code);
    }
}
