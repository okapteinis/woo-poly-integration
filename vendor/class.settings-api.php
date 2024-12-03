<?php

declare(strict_types=1);

class Settings_API
{
    protected array $settings_sections = [];
    protected array $settings_fields = [];

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    public function admin_enqueue_scripts(): void
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_media();
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
    }

    public function set_sections(array $sections): self
    {
        $this->settings_sections = $sections;
        return $this;
    }

    public function add_section(array $section): self
    {
        $this->settings_sections[] = $section;
        return $this;
    }

    public function set_fields(array $fields): self
    {
        $this->settings_fields = $fields;
        return $this;
    }

    public function add_field(string $section, array $field): self
    {
        $defaults = [
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text'
        ];
        $this->settings_fields[$section][] = wp_parse_args($field, $defaults);
        return $this;
    }

    public function admin_init(): void
    {
        $this->register_settings_sections();
        $this->register_settings_fields();
        $this->register_settings();
    }

    private function register_settings_sections(): void
    {
        foreach ($this->settings_sections as $section) {
            if (get_option($section['id']) === false) {
                add_option($section['id']);
            }

            $callback = $this->get_section_callback($section);
            add_settings_section(
                $section['id'],
                $section['title'],
                $callback,
                $section['id']
            );
        }
    }

    private function get_section_callback(array $section): ?callable
    {
        if (!empty($section['desc'])) {
            return function() use ($section) {
                echo wp_kses_post(
                    sprintf('%s', $section['desc'])
                );
            };
        }
        return $section['callback'] ?? null;
    }

    private function register_settings_fields(): void
    {
        foreach ($this->settings_fields as $section => $field) {
            foreach ($field as $option) {
                $type = $option['type'] ?? 'text';
                $callback = $option['callback'] ?? [$this, 'callback_' . $type];
                $args = $this->prepare_field_args($option, $section);
                add_settings_field(
                    "{$section}[{$option['name']}]",
                    $option['label'] ?? '',
                    $callback,
                    $section,
                    $section,
                    $args
                );
            }
        }
    }

    private function prepare_field_args(array $option, string $section): array
    {
        return [
            'id' => $option['name'],
            'class' => $option['class'] ?? $option['name'],
            'label_for' => "{$section}[{$option['name']}]",
            'desc' => $option['desc'] ?? '',
            'name' => $option['label'] ?? '',
            'section' => $section,
            'size' => $option['size'] ?? null,
            'options' => $option['options'] ?? '',
            'std' => $option['default'] ?? '',
            'sanitize_callback' => $option['sanitize_callback'] ?? '',
            'type' => $option['type'] ?? 'text',
            'placeholder' => $option['placeholder'] ?? '',
            'min' => $option['min'] ?? '',
            'max' => $option['max'] ?? '',
            'step' => $option['step'] ?? '',
        ];
    }
}
