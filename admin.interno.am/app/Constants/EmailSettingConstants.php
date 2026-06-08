<?php

namespace App\Constants;

class EmailSettingConstants
{
    const TYPE_ORDER_ON_HOLD = 'order_on_hold';
    const TYPE_ORDER_PROCESSING = 'order_processing';
    const TYPE_ORDER_COMPLETED = 'order_completed';
    const TYPE_ADMIN_NEW_ORDER = 'admin_new_order';
    const TYPE_ORDER_REFUND = 'order_refund';
    const TYPE_ACCOUNT_RESET_PASSWORD = 'account_reset_password';
    const TYPE_EMAIL_CONFIRMATION_REVIEW_GUST = 'email_confirmation_review_gust';
    const TYPE_ORDER_CANCELED = 'order_cancelled';
    const TYPE_OFFER_CREATED_EMAIL = 'offer_created_email';
    const TYPE_OFFER_UPDATED_EMAIL = 'offer_updated_email';
    const TYPE_GIFT_CARD_EMAIL = 'gift_card_email';
    const TYPE_EMAIL_TO_CUSTOMER = 'order_customer_email';
    const TYPE_EMAIL_REGISTRATION_CONFIRMATION = 'registration_confirmation_email';
    const TYPE_EMAIL_LOYALTY_CLUB_ORDER_CONFIRMATION = 'loyalty_club_order_confirmation_email';
    const TYPE_EMAIL_LOYALTY_CLUB_NEW_LEVEL = 'loyalty_club_new_level_email';
    const TYPE_EMAIL_LOYALTY_CLUB_CASHBACK = 'loyalty_club_cashback_email';
    const TYPE_EMAIL_CART_SHARE = 'cart_share_to_email';

}
