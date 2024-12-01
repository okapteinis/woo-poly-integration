<?php

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

class Categories implements TaxonomiesInterface
{
    public function __construct()
    {
        add_action('product_cat_add_form_fields', [$this, 'copyProductCatCustomFields'], 11);
        add_action('created_term', [$this, 'syncProductCatCustomFields'], 11, 3);
        add_action('edit_term', [$this, 'syncProductCatCustomFields'], 11, 3);
    }

    public function syncProductCatCustomFields(int $termID, int $ttID = 0, string $taxonomy = ''): void
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
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#display_type option[value="<?php echo esc_attr($type); ?>"]')
                    .prop("selected", true);
                $('#product_cat_thumbnail img').attr('src', '<?php echo esc_url($image); ?>');
                $('#product_cat_thumbnail_id').val('<?php echo esc_attr($thumbID); ?>');
                <?php if ($thumbID): ?>
                    $('.remove_image_button').show();
                <?php endif; ?>
            });
        </script>
        <?php
    }

    public function doSyncProductCatCustomFields(int $ID, string $key, mixed $value = ''): void
    {
        $translations = Utilities::getTermTranslationsArrayByID($ID);
        foreach ($translations as $translation) {
            update_term_meta($translation, $key, $value);
        }
    }

    public static function getNames(): array
    {
        return ['product_cat'];
    }
}
