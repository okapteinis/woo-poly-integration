<?php

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Utilities;

class Features extends AbstractSettings
{
    public static function getID(): string
    {
        return 'wpi-features';
    }

    protected function doGetSections(): array
    {
        return [
            [
                'title' => __('Features', 'woo-poly-integration'),
                'desc' => sprintf(
                    '%s %s <a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki/Settings---Features">%s</a>.',
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
            // ... citi lauki ar līdzīgu struktūru
            [
                'name' => 'new-translation-defaults',
                'type' => 'radio',
                'default' => '0',
                'label' => __('New Translation Behaviour', 'woo-poly-integration'),
                'desc' => __('When creating new translations, start with blank text, copy or machine translation? (You may want to turn this off if using Polylang Pro, Lingotek or other automatic copy-or-translation solution.)', 'woo-poly-integration'),
                'options' => [
                    '0' => 'Blank Text',
                    '1' => __('Copy Source', 'woo-poly-integration'),
                    '2' => __('Translate Source', 'woo-poly-integration') . ' (coming soon..  until available will use Copy Source) ',
                ]
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
