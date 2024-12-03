<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;

class MetasList extends AbstractSettings
{
    public static function getID(): string
    {
        return 'wpi-metas-list';
    }

    protected function doGetSections(): array
    {
        return apply_filters(HooksInterface::SETTINGS_SECTIONS_FILTER, []);
    }

    protected function doGetFields(): array
    {
        return apply_filters(HooksInterface::SETTINGS_FIELDS_FILTER, []);
    }

    protected function dorender(array $sections, array $fields): void
    {
        $this->addSections($sections);
        $this->addFields($fields);
    }

    protected function addSections(array $sections): void
    {
        $sections[] = [
            'id' => static::getID(),
            'title' => __('Metas List', 'woo-poly-integration'),
            'desc' => __(
                'The section will allow you to control which metas should be synced',
                'woo-poly-integration'
            )
        ];

        parent::addSections($sections);
    }

    protected function addFields(array $fields): void
    {
        parent::addFields($fields, static::getID());
    }
}
