<?php

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Product\Meta;

class MetasList extends AbstractSettings
{
    public static function getID(): string
    {
        return 'wpi-metas-list';
    }

    protected function doGetSections(): array
    {
        return [
            [
                'title' => __('Metas List', 'woo-poly-integration'),
                'desc' => sprintf(
                    '%s %s <a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki/Settings-Metas">%s</a>.',
                    __(
                        'The section will allow you to control which metas should be
                         synced between products and their translations. The default
                         values are appropriate for the large majority of the users.
                         It is safe to ignore these settings if you do not understand
                         their meaning. Please ignore this section if you do not
                         understand the meaning of this.',
                        'woo-poly-integration'
                    ),
                    __('For more information please see:', 'woo-poly-integration'),
                    __('documentation pages', 'woo-poly-integration')
                ),
            ],
        ];
    }

    protected function doGetFields(): array
    {
        $metas = Meta::getProductMetaToCopy([], false);
        $fields = [];

        foreach ($metas as $ID => $value) {
            $fields[] = [
                'name' => $ID,
                'label' => $value['name'],
                'desc' => $value['desc'],
                'type' => 'multicheck',
                'default' => array_combine($value['metas'], $value['metas']),
                'options' => array_combine(
                    $value['metas'],
                    array_map([__CLASS__, 'normalize'], $value['metas'])
                ),
            ];
        }

        return $fields;
    }

    public static function normalize(string $string): string
    {
        return ucwords(trim(str_replace('_', ' ', $string)));
    }
}
