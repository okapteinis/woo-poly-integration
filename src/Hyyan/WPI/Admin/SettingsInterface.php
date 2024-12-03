<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

interface SettingsInterface
{
    public function getSections(array $sections): array;

    public function getFields(array $fields): array;

    public static function getID(): string;
}
