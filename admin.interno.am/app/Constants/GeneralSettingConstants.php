<?php

namespace App\Constants;

class GeneralSettingConstants
{
    const PRIVATE_GENERAL_SETTINGS_KEYS = [
        'pinterest_account_id', 'pinterest_access_token', 'pinterest_script_id', 'google_tag_key', 'cart_share_commission_agent_percentage',
        'cart_share_reminder_email_days', 'cart_share_set_commission_if_discount_less_or_equal', 'newsletter_token', 'newsletter_list_uid', 'offer_commission_agent_percentage',
        'per_package_cost', 'provider_user_group_id', 'gb_notax_max_amount', 'vatsense_api_key', 'paypal_sandbox_client_id', 'paypal_sandbox_client_secret',
        'paypal_live_client_id', 'paypal_live_client_secret', 'paypal_mode', 'stripe_mode', 'stripe_key_live', 'stripe_secret_live', 'stripe_webhook_key_live',
        'stripe_key_sandbox', 'stripe_webhook_key_sandbox', 'admin_domain', 'cloudflare_zone_id', 'cloudflare_api_token', 'chat_gpt_token',
        'chatling_token', 'chatling_chatbot_id', 'amazon_ses_region', 'amazon_ses_credentials_key', 'amazon_ses_credentials_secret', 'opencagedata_id',
        'dhl_api_token', 'tracking_page_id'
    ];

    const GENERAL_SETTINGS_GROUP_GENERAL = 0;
    const GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS = 1;
    const GENERAL_SETTINGS_GROUP_TRACKERS = 2;
    const GENERAL_SETTINGS_GROUP_MARKETING = 3;
    const GENERAL_SETTINGS_GROUP_CHAT = 4;
    const GENERAL_SETTINGS_GROUP_VAT_CHECKER = 5;
    const GENERAL_SETTINGS_PAYPAL = 6;
    const GENERAL_SETTINGS_STRIPE = 7;
    const GENERAL_SETTINGS_TRUSTPILOT = 8;
    const GENERAL_SETTINGS_OUTLOOK = 9;
    const GENERAL_CACHE = 10;
    const GENERAL_SETTINGS_AI = 11;
    const GENERAL_SETTINGS_AMAZON_SES = 12;
    const GENERAL_SETTINGS_CONTENT = 13;

    const GENERAL_SETTINGS_GROUPS = [
      self::GENERAL_SETTINGS_GROUP_GENERAL => 'General',
      self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS => 'Contact & address',
      self::GENERAL_SETTINGS_GROUP_TRACKERS => 'Trackers',
      self::GENERAL_SETTINGS_GROUP_MARKETING => 'Marketing',
      self::GENERAL_SETTINGS_GROUP_CHAT => 'Chats',
      self::GENERAL_SETTINGS_GROUP_VAT_CHECKER => 'VAT checker',
      self::GENERAL_SETTINGS_PAYPAL => 'PayPal',
      self::GENERAL_SETTINGS_STRIPE => 'Stripe',
      self::GENERAL_SETTINGS_TRUSTPILOT => 'Trustpilot',
      self::GENERAL_SETTINGS_OUTLOOK => 'Outlook',
      self::GENERAL_SETTINGS_AMAZON_SES => 'Amazon ses',
      self::GENERAL_CACHE => 'Cache',
      self::GENERAL_SETTINGS_AI => 'AI',
      self::GENERAL_SETTINGS_CONTENT => 'Content',
    ];

    const GENERAL_SETTINGS_KEY = [
        'site_name' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'company' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'logo' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'admin_panel_logo' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'logo_email' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'placeholder_image' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'social_img_default' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'timezone' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'default_seo_suffix' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'address' => self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS,
        'email' => self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS,
        'phone' => self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS,
        'whatsapp_number' => self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS,
        'prices_include_taxes' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'delivery_working_days' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'anytrack_key' => self::GENERAL_SETTINGS_GROUP_TRACKERS,
        'pinterest_account_id' => self::GENERAL_SETTINGS_GROUP_TRACKERS,
        'pinterest_access_token' => self::GENERAL_SETTINGS_GROUP_TRACKERS,
        'trustpilot' => self::GENERAL_SETTINGS_TRUSTPILOT,
        'pinterest_script_id' => self::GENERAL_SETTINGS_GROUP_TRACKERS,
        'google_tag_key' => self::GENERAL_SETTINGS_GROUP_TRACKERS,
        'offer_commission_agent_percentage' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'cart_share_commission_agent_percentage' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'cart_share_reminder_email_days' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'cart_share_set_commission_if_discount_less_or_equal' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'company_address' => self::GENERAL_SETTINGS_GROUP_CONTACT_AND_ADDRESS,
        'zoho_widget_code' => self::GENERAL_SETTINGS_GROUP_CHAT,
        'header_info' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'newsletter_token' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'newsletter_list_uid' => self::GENERAL_SETTINGS_GROUP_MARKETING,
        'use_ip_checker' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'per_package_cost' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'provider_user_group_id' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'gb_notax_max_amount' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'vatsense_api_key' => self::GENERAL_SETTINGS_GROUP_VAT_CHECKER,
        'google_api_key' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'opencagedata_id' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'paypal_sandbox_client_id' => self::GENERAL_SETTINGS_PAYPAL,
        'paypal_sandbox_client_secret' => self::GENERAL_SETTINGS_PAYPAL,
        'paypal_live_client_id' => self::GENERAL_SETTINGS_PAYPAL,
        'paypal_live_client_secret' => self::GENERAL_SETTINGS_PAYPAL,
        'paypal_mode' => self::GENERAL_SETTINGS_PAYPAL,
        'stripe_mode' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_key_live' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_secret_live' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_webhook_key_live' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_key_sandbox' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_secret_sandbox' => self::GENERAL_SETTINGS_STRIPE,
        'stripe_webhook_key_sandbox' => self::GENERAL_SETTINGS_STRIPE,
        'site_domain' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'admin_domain' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'cloudflare_zone_id' => self::GENERAL_CACHE,
        'cloudflare_api_token' => self::GENERAL_CACHE,
        'chat_gpt_token' => self::GENERAL_SETTINGS_AI,
        'chatling_token' => self::GENERAL_SETTINGS_AI,
        'chatling_chatbot_id' => self::GENERAL_SETTINGS_AI,
        'amazon_ses_region' => self::GENERAL_SETTINGS_AMAZON_SES,
        'amazon_ses_credentials_key' => self::GENERAL_SETTINGS_AMAZON_SES,
        'amazon_ses_credentials_secret' => self::GENERAL_SETTINGS_AMAZON_SES,
        'chatling_widget_id' => self::GENERAL_SETTINGS_AI,
        'menu_styles' => self::GENERAL_SETTINGS_CONTENT,
        'dhl_api_token' => self::GENERAL_SETTINGS_GROUP_GENERAL,
        'tracking_page_id' => self::GENERAL_SETTINGS_GROUP_GENERAL,
    ];
}
