<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Admin\Settings;

abstract class AbstractSettings implements SettingsInterface
{
    protected Settings_API $settingsApi;
    protected array $sections = [];
    protected array $fields = [];

    public function __construct()
    {
        $this->init();
        $this->addSections();
        $this->addFields();
        $this->settingsApi = new Settings_API();
        $this->settingsApi->set_sections($this->sections);
        $this->settingsApi->set_fields($this->fields);
        $this->settingsApi->admin_init();
    }

    abstract protected function init(): void;

    abstract protected function addSections(): void;

    abstract protected function addFields(): void;
}
