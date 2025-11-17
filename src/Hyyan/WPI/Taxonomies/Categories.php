<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Security\Nonce;

/**
 * Categories.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Categories implements TaxonomiesInterface
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        /* Handle product category custom fiedlds */
        add_action(
                'product_cat_add_form_fields', array($this, 'copyProductCatCustomFields'), 11
        );
        add_action(
                'created_term', array($this, 'syncProductCatCustomFields'), 11, 3
        );
        add_action(
                'edit_term', array($this, 'syncProductCatCustomFields'), 11, 3
        );
    }

    /**
     * Sync Product Category Custom Fields.
     *
     * Keep product categories translation synced
     *
     * @since 1.7.0 Added nonce verification for security
     * @param int    $termID   the term id
     * @param int    $ttID     ?
     * @param string $taxonomy the taxonomy name
     */
    public function syncProductCatCustomFields($termID, $ttID = '', $taxonomy = '')
    {
        // Only process if it's a product category
        if ('product_cat' !== $taxonomy) {
            return;
        }

        // Verify nonce - WordPress should have validated this, but verify defensively
        // Check for both tag and category nonces as WordPress uses different ones
        $nonce_verified = false;
        if (isset($_POST['_wpnonce'])) {
            $nonce_verified = wp_verify_nonce($_POST['_wpnonce'], 'update-tag_' . $termID) ||
                            wp_verify_nonce($_POST['_wpnonce'], 'add-tag');
        }

        // For AJAX requests, check ajax nonce
        if (!$nonce_verified && defined('DOING_AJAX') && DOING_AJAX) {
            if (isset($_POST['security'])) {
                $nonce_verified = wp_verify_nonce($_POST['security'], 'add-tag');
            }
        }

        // If we're in admin and nonce not verified, skip for security
        if (is_admin() && !$nonce_verified && !defined('WP_CLI')) {
            return;
        }

        if (isset($_POST['display_type'])) {
            $this->doSyncProductCatCustomFields(
                    $termID, 'display_type', sanitize_text_field($_POST['display_type'])
            );
        }
        if (isset($_POST['product_cat_thumbnail_id'])) {
            $this->doSyncProductCatCustomFields(
                    $termID, 'thumbnail_id', absint($_POST['product_cat_thumbnail_id'])
            );
        }

        if ('product_cat' === $taxonomy) {
            /* Allow other plugins to check for category custom fields */
            do_action(
                    HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS, $this, $termID, $taxonomy
            );
        }
    }

    /**
     * Copy product Category Custom fields.
     *
     * Copy the category custom fields from orginal category to its translations
     * when we start adding new category translation
     *
     * @return bool false if this action must not be executed
     */
    public function copyProductCatCustomFields()
    {

        /* We sync custom fields only for translation */
        if (!(isset($_GET['from_tag']) && isset($_GET['new_lang']))) {
            return false;
        }

        $ID = absint($_GET['from_tag']);
        $type = get_term_meta($ID, 'display_type', true);
        $thumbID = absint(get_term_meta($ID, 'thumbnail_id', true));
        $image = $thumbID ?
                wp_get_attachment_thumb_url($thumbID) :
                wc_placeholder_img_src(); ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#display_type option[value="<?php echo esc_js($type); ?>"]')
                        .prop("selected", true);
                $('#product_cat_thumbnail img').attr('src', '<?php echo esc_url($image); ?>');
                $('#product_cat_thumbnail_id').val('<?php echo absint($thumbID); ?>');
        <?php if ($thumbID): ?>
                    $('.remove_image_button').show();
        <?php endif; ?>
            });
        </script>
        <?php

        /* Allow other plugins to check for category custom fields */
        do_action(
                HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS, $ID
        );
    }
    /**
     * Do sync category custom fields.
     *
     * @param int    $ID    the term ID
     * @param string $key   the key
     * @param mixed  $value the value
     */
    public function doSyncProductCatCustomFields($ID, $key, $value = '')
    {
        $translations = Utilities::getTermTranslationsArrayByID($ID);

        foreach ($translations as $translation) {
			      update_term_meta( $translation, $key, $value );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getNames()
    {
        return array('product_cat');
    }
}
