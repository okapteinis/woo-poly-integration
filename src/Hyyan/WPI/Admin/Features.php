<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

class Features
{
    public const ID = 'wpi-features';

    public function getID(): string
    {
        return self::ID;
    }

    protected function getSections(): array
    {
        return [
            [
                'id' => self::ID,
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

    protected function getFields(): array
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
