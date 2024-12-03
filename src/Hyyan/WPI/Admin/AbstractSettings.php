<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

abstract class AbstractSettings
{
    public function getSections(array $sections): array
    {
        $current = apply_filters(
            $this->getSectionsFilterName(),
            (array) $this->doGetSections()
        );

        $new = [];
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
