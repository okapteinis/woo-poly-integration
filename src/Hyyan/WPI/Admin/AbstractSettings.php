<?php

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;

abstract class AbstractSettings implements SettingsInterface
{
    public function __construct()
    {
        add_filter(
            HooksInterface::SETTINGS_SECTIONS_FILTER,
            [$this, 'getSections']
        );
        add_filter(
            HooksInterface::SETTINGS_FIELDS_FILTER,
            [$this, 'getFields']
        );
    }

    public function getSections(array $sections): array
    {
        $new = [];
        $current = apply_filters(
            $this->getSectionsFilterName(),
            (array) $this->doGetSections()
        );

        foreach ($current as $def) {
            $def['id'] = static::getID();
            $new[static::getID()] = $def;
        }

        return array_merge($sections, $new);
    }

    public function getFields(array $fields): array
    {
        return array_merge($fields, [
            static::getID() => apply_filters(
                $this->getFieldsFilterName(),
                (array) $this->doGetFields()
            ),
        ]);
    }

    protected function getSectionsFilterName(): string
    {
        return sprintf('woo-poly.settings.%s_sections', static::getID());
    }

    protected function getFieldsFilterName(): string
    {
        return sprintf('woo-poly.settings.%s_fields', static::getID());
    }

    abstract protected function doGetSections(): array;

    abstract protected function doGetFields(): array;
}
