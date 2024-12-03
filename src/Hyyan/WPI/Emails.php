<?php

declare(strict_types=1);

namespace Hyyan\WPI;

use WC_Email;
use WC_Emails;
use WC_Order;
use WP_User;
use stdClass;

class Emails
{
    public function __construct()
    {
        $this->registerEmailStringsForTranslation();

        add_filter('woocommerce_email_subject_new_order', [$this, 'filter_email_subject'], 10, 3);
        add_filter('woocommerce_email_heading_new_order', [$this, 'filter_email_heading'], 10, 3);
        add_filter('woocommerce_email_footer_text', [$this, 'translateCommonString']);
        add_filter('woocommerce_email_from_address', [$this, 'translateCommonString']);
        add_filter('woocommerce_email_from_name', [$this, 'translateCommonString']);
        add_filter('woocommerce_email_setup_locale', [$this, 'reset_lang_switch']);
        add_filter('woocommerce_email_restore_locale', [$this, 'reset_lang_switch']);

        do_action(HooksInterface::EMAILS_TRANSLATION_ACTION, $this);
    }

    public function get_default_setting(string $string_type, string $email_type): string
    {
        $wc_emails = WC_Emails::instance();
        $emails = $wc_emails->get_emails();
        $emailobj = new stdClass();
        $return = '';

        $emailobj = match($email_type) {
            'new_order' => $emails['WC_Email_New_Order'],
            'cancelled_order' => $emails['WC_Email_Cancelled_Order'],
            default => $emailobj
        };

        if ($emailobj instanceof WC_Email) {
            $return = match($string_type) {
                'subject_full', 'subject' => $emailobj->get_default_subject(),
                'heading_full', 'heading' => $emailobj->get_default_heading(),
                'additional_content' => $emailobj->get_default_additional_content(),
                'subject_partial', 'subject_paid' => $emailobj->get_default_subject(true),
                'heading_partial', 'heading_paid' => $emailobj->get_default_heading(true),
                default => ''
            };
        }

        return apply_filters(HooksInterface::EMAILS_DEFAULT_SETTING_FILTER, $return, $string_type, $email_type);
    }

    public function translateEmailStringToObjectLanguage(
        string $formatted_string,
        WC_Order|WP_User $target_object,
        string $string_type,
        WC_Email $email_obj
    ): string {
        $target_language = $this->maybeSwitchLanguage($target_object);
        $email_type = $email_obj->id;

        $string_type = match($email_type) {
            'customer_partially_refunded_order' => $this->handlePartialRefund($string_type),
            'customer_refunded_order' => $this->handleFullRefund($string_type),
            'customer_invoice' => $this->handleInvoice($string_type, $target_object),
            default => $string_type
        };

        $string_template = $this->getEmailSetting($string_type, $email_type) 
            ?: $this->get_default_setting($string_type, $email_type);

        if (!$string_template) {
            return $formatted_string;
        }

        if ($target_object instanceof WC_Order) {
            return $this->formatOrderString($string_template, $target_object, $email_obj);
        }

        return $formatted_string;
    }

    private function handlePartialRefund(string $string_type): string
    {
        return match($string_type) {
            'subject', 'heading' => $string_type . '_partial',
            default => $string_type
        };
    }

    private function handleFullRefund(string $string_type): string
    {
        return match($string_type) {
            'subject', 'heading' => $string_type . '_full',
            default => $string_type
        };
    }

    private function handleInvoice(string $string_type, WC_Order $order): string
    {
        return match($string_type) {
            'subject', 'heading' => $order->has_status(wc_get_is_paid_statuses()) 
                ? $string_type . '_paid' 
                : $string_type,
            default => $string_type
        };
    }
}
