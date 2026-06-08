<?php

namespace App\Constants;

class UserLevelConstants
{
    const REFUND_DAY = 14;

    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED = 3;

    const STATUSES = [
        'Processing' => self::STATUS_PROCESSING,
        'Completed' => self::STATUS_COMPLETED,
        'Cancelled' => self::STATUS_CANCELLED,
    ];

    const USER_LEVEL_OPTIONS = [
        'access_purchase_receipts' => 'Digital access to all purchase receipts',
        'access_premium_content' => 'Access to premium content, videos, & tutorials',
        'free_shipping' => 'Free shipping on all orders',
        'workshops_seminars' => 'Workshops and online seminars',
        '30_day_return' => '30-day return policy',
        'same_day_shipping' => 'Same-day shipping',
        'express_delivery' => 'Express / 24-hour delivery',
        'access_product_shortages' => 'Priority access in case of product shortages',
        'priority_support' => 'Priority customer support via a dedicated hotline',
        'invoice_payment' => 'Invoice payment with 30-day payment term',
        'invitations_events' => 'Invitations to exclusive club events',
        'access_new_products' => 'First access to new products',
        'free_product_samples' => 'Free product samples',
    ];
}
