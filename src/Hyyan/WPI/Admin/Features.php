<?php

declare(strict_types=1);

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;

class Features extends AbstractSettings
{
    public static function getID(): string
    {
        return 'wpi-features';
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
            'title' => __('Features', 'woo-poly-integration'),
            'desc' => __(
                'The section will allow you to Enable/Disable plugin features',
                'woo-poly-integration'
            )
        ];

        parent::addSections($sections);
    }

    protected function addFields(array $fields): void
    {
        $fields[] = [
            'name' => 'stock',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Stock', 'woo-poly-integration'),
            'desc' => __(
                'Sync stock for product between languages. <strong>Note: this is an important feature if you are using a multi-language setup and need to share stock between different languages.</strong>',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'localenumbers',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Localise Numbers', 'woo-poly-integration'),
            'desc' => __(
                'Format numbers according to the current locale',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'importsync',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Import', 'woo-poly-integration'),
            'desc' => __(
                'Sync product import between languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'checkpages',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Check Pages', 'woo-poly-integration'),
            'desc' => __(
                'Check that WooCommerce pages are translated and show warning if not',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'reports',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Reports', 'woo-poly-integration'),
            'desc' => __(
                'Enable reports language filtering and combining',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'emails',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Emails', 'woo-poly-integration'),
            'desc' => __(
                'Enable emails language filtering and combining',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'coupons',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Coupons Sync', 'woo-poly-integration'),
            'desc' => __(
                'Apply coupons rules in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'payment-gateways',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Payment Gateways Sync', 'woo-poly-integration'),
            'desc' => __(
                'Apply Payment Gateways rules in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'new-translation',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('New Translation Copying', 'woo-poly-integration'),
            'desc' => __(
                'Enable at least one option if you want to translate products automatically. [You can later disable this option]',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'variable-sync',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Variable Products', 'woo-poly-integration'),
            'desc' => __(
                'Should Variable Product translations be sychronized by default',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'categories',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Categories', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same product categories in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'tags',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Tags', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same product tags in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'attributes',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Attributes', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same product attributes in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'shipping-class',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Shipping Classes', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same product shipping classes in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'images',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Images', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same product images in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'total_sales',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Total Sales', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same total sales in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'downloadable',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Download Files', 'woo-poly-integration'),
            'desc' => __(
                'Apply the same downloadable files in all languages',
                'woo-poly-integration'
            )
        ];

        $fields[] = [
            'name' => 'metadata',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Sync Metadata', 'woo-poly-integration'),
            'desc' => __(
                'Sync all custom fields in all languages',
                'woo-poly-integration'
            )
        ];

        parent::addFields($fields, static::getID());
    }
}
