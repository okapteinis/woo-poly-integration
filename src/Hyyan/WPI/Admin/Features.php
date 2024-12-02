<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

class Features
{
    public function getID(): string
    {
        return 'wpi-features';
    }

    protected function getSections(): array
    {
        return [
            [
                'id' => 'wpi-features',
                'title' => __('Features', 'woo-poly-integration'),
                'desc' => sprintf(
                    '%s %s %s.',
                    __('The section will allow you to Enable/Disable Plugin Features.', 'woo-poly-integration'),
                    __('For more information please see:', 'woo-poly-integration'),
                    __('documentation pages', 'woo-poly-integration')
                ),
            ],
        ];
    }

    protected function doGetFields(): array
    {
        return [
            [
                'name' => 'fields-locker',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Fields Locker', 'woo-poly-integration'),
                'desc' => __('Locks Meta fields which are set to be synchronized.', 'woo-poly-integration'),
            ],
            [
                'name' => 'emails',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Emails', 'woo-poly-integration'),
                'desc' => __('Use order language whenever WooCommerce sends order emails', 'woo-poly-integration'),
            ],
            [
                'name' => 'reports',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Reports', 'woo-poly-integration'),
                'desc' => __('Enable reports language filtering and combining', 'woo-poly-integration'),
            ],
            [
                'name' => 'coupons',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Coupons Sync', 'woo-poly-integration'),
                'desc' => __('Apply coupons rules through languages', 'woo-poly-integration'),
            ],
            [
                'name' => 'stock',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Stock Sync', 'woo-poly-integration'),
                'desc' => __('Sync stock for product variations and their translations', 'woo-poly-integration'),
            ],
            [
                'name' => 'categories',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Categories', 'woo-poly-integration'),
                'desc' => __('Enable categories translations', 'woo-poly-integration'),
            ],
            [
                'name' => 'tags',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Tags', 'woo-poly-integration'),
                'desc' => __('Enable tags translations', 'woo-poly-integration'),
            ],
            [
                'name' => 'attributes',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Attributes', 'woo-poly-integration'),
                'desc' => __('Enable attributes translations', 'woo-poly-integration'),
            ],
            [
                'name' => 'localenumbers',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Localise Numbers', 'woo-poly-integration'),
                'desc' => __('Use locale number formats in prices, weights and dimensions', 'woo-poly-integration'),
            ],
            [
                'name' => 'importsync',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Synchronize on Import', 'woo-poly-integration'),
                'desc' => __('When importing translations using Polylang Import/Export, synchronize Woocommerce meta', 'woo-poly-integration'),
            ],
            [
                'name' => 'new-translation-defaults',
                'type' => 'radio',
                'default' => '0',
                'label' => __('New Translation Behaviour', 'woo-poly-integration'),
                'desc' => __('When creating new translations, start with blank text, copy or machine translation? (You may want to turn this off if using Polylang Pro, Lingotek or other automatic copy-or-translation solution.)', 'woo-poly-integration'),
                'options' => [
                    '0' => 'Blank Text',
                    '1' => __('Copy Source', 'woo-poly-integration'),
                    '2' => __('Translate Source', 'woo-poly-integration') . ' (coming soon.. until available will use Copy Source) ',
                ],
            ],
            [
                'name' => 'checkpages',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Check WooCommerce Pages', 'woo-poly-integration'),
                'desc' => __('Check if WooCommerce pages are present and correctly translated and published. Especially helpul on initial setup', 'woo-poly-integration'),
            ],
        ];
    }
}
