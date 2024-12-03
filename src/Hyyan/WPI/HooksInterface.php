<?php

declare(strict_types=1);

namespace Hyyan\WPI;

interface HooksInterface
{
    public const PRODUCT_META_SYNC_FILTER = 'woo-poly.product.metaSync';
    public const FIELDS_LOCKER_SELECTORS_FILTER = 'woo-poly.fieldsLockerSelectors';
    public const FIELDS_LOCKER_VARIABLE_EXCLUDE_SELECTORS_FILTER = 'woo-poly.fieldsLockerVariableExcludeSelectors';
    public const PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS = 'woo-poly.product.syncCategoryCustomFields';
    public const PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS = 'woo-poly.product.copyCategoryCustomFields';
    public const PAGES_LIST = 'woo-poly.pages.list';
    public const SETTINGS_SECTIONS_FILTER = 'woo-poly.settings.sections';
    public const SETTINGS_FIELDS_FILTER = 'woo-poly.settings.fields';
    public const LANGUAGE_REPO_URL_FILTER = 'woo-poly.language.repoUrl';
    public const GATEWAY_LOAD_EXTENTION = 'woo-poly.gateway.loadClassExtention.';
    public const PRODUCT_VARIATION_COPY_META_ACTION = 'woo-poly.product.variation.copyMeta';
    public const PRODUCT_DISABLED_META_SYNC_FILTER = 'woo-poly.product.disabledMetaSync';
    public const EMAILS_TRANSLATABLE_FILTER = 'woo-poly.Emails.translatableEmails';
    public const EMAILS_DEFAULT_SETTINGS_FILTER = 'woo-poly.Emails.defaultSettings';
    public const EMAILS_DEFAULT_SETTING_FILTER = 'woo-poly.Emails.defaultSetting';
    public const EMAILS_TRANSLATION_ACTION = 'woo-poly.Emails.translation';
    public const EMAILS_SWITCH_LANGUAGE_ACTION = 'woo-poly.Emails.switchLanguage';
    public const EMAILS_AFTER_SWITCH_LANGUAGE_ACTION = 'woo-poly.Emails.afterSwitchLanguage';
    public const EMAILS_ORDER_FIND_REPLACE_FIND_FILTER = 'woo-poly.Emails.orderFindReplaceFind';
    public const EMAILS_ORDER_FIND_REPLACE_REPLACE_FILTER = 'woo-poly.Emails.orderFindReplaceReplace';
    public const CART_SWITCHED_ITEM = 'woo-poly.Cart.switchedItem';
}
