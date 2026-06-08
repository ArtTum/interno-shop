<?php

namespace App\Constants;

class OrderConstants
{
    const ORDER_STATUS_TO_WAREHOUSE = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_PROCESSING => 'processing',
        self::STATUS_COMPLETED => 'completed',
        self::STATUS_ON_HOLD => 'on-hold',
        self::STATUS_FAILED => 'failed',
        self::STATUS_REFUNDED => 'refunded',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_TRASH => 'trash',
    ];

    const DISPUTE_ANOTHER = 4;
    const DISPUTE_WON = 3;
    const DISPUTE_LOST = 2;
    const DISPUTE_IN_PROCESS = 1;

    const PAYPAL_DISPUTE_WIN_CONSTANTS = [
        "RESOLVED_SELLER_FAVOUR",
        "CANCELED_BY_BUYER" // Buyer canceled the dispute — good for seller
    ];

    const PAYPAL_DISPUTE_LOSE_CONSTANTS = [
        "RESOLVED_BUYER_FAVOUR"
    ];

    const PAYPAL_DISPUTE_ANOTHER = [
        "RESOLVED_WITH_PAYOUT", // PayPal may pay either party, check `money_movements`
        "NONE",                 // No clear resolution, or overridden
        "ACCEPTED",             // Generic, context-dependent
        "DENIED"                // Generic, context-dependent
    ];

    const STATUS_TRASH = 8;
    const STATUS_ON_HOLD = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_FAILED = 7;
    const STATUS_REFUNDED = 6;
    const STATUS_PROCESSING = 2;
    const STATUS_PENDING = 1;

    const STATUSES = [
        'Pending' => self::STATUS_PENDING,
        'Processing' => self::STATUS_PROCESSING,
        'On hold' => self::STATUS_ON_HOLD,
        'Completed' => self::STATUS_COMPLETED,
        'Cancelled' => self::STATUS_CANCELLED,
        'Refunded' => self::STATUS_REFUNDED,
        'Failed' => self::STATUS_FAILED,
        'Trash' => self::STATUS_TRASH,
    ];

    const STATUSES_FOR_DOCUMENTS = [
        'Processing' => 2,
        'On hold' => 3,
        'Completed' => 4,
    ];

    const ORDER_FINAL_STATUSES = [
        'completed' => OrderConstants::STATUS_COMPLETED,
        'cancelled' => OrderConstants::STATUS_CANCELLED,
        'failed' => OrderConstants::STATUS_FAILED,
        'refunded' => OrderConstants::STATUS_REFUNDED,
        'reshipment' => OrderConstants::STATUS_COMPLETED,
    ];

    const USED_DISCOUNT_TYPES = [
        'coupon' => 1,
        'gift' => 2
    ];

    const WAREHOUSE_STATUSES = [
        'Not processed' => 0,
        'In progress' => 1,
        'Not available' => 2,
        'Cancelled' => 3,
        'Completed' => 4,
        'Complete later' => 5,
        'Failed' => 6,
    ];

    const ORDER_CART_RESTORE_PATH = '?resc=';
    const CART_SHARE_PATH = '?shcar=';
    const OFFER_PATH = '?offepo=';
    const PROFORMA_INVOICE = 3;

    const BULK_ACTIONS = [
        'Change status to processing' => 2,
        'Change status to on-hold' => 3,
        'Change status to completed' => 4,
        'Change status to cancelled' => 5,
        'Change status to trash' => self::STATUS_TRASH,
    ];

    const EUROPEAN_COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT',
        'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
    ];

    const VAT_NUMBERS_VALIDATION_PATTERNS = [
        'AT' => '/^ATU\d{8}$/',                // Austria
        'BE' => '/^BE[01]?\d{9}$/',            // Belgium
        'BG' => '/^BG\d{9,10}$/',              // Bulgaria
        'CY' => '/^CY\d{8}[A-Z]$/',            // Cyprus
        'CZ' => '/^CZ\d{8,10}$/',              // Czech Republic
        'DE' => '/^DE\d{9}$/',                 // Germany
        'DK' => '/^DK\d{8}$/',                 // Denmark
        'EE' => '/^EE\d{9}$/',                 // Estonia
        'EL' => '/^EL\d{9}$/',                 // Greece
        'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/', // Spain
        'FI' => '/^FI\d{8}$/',                 // Finland
        'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',      // France
        'HR' => '/^HR\d{11}$/',                // Croatia
        'HU' => '/^HU\d{8}$/',                 // Hungary
        'IE' => '/^IE\d{7}[A-Z]{1,2}$/',       // Ireland
        'IT' => '/^IT\d{11}$/',                // Italy
        'LT' => '/^LT\d{9,12}$/',              // Lithuania
        'LU' => '/^LU\d{8}$/',                 // Luxembourg
        'LV' => '/^LV\d{11}$/',                // Latvia
        'MT' => '/^MT\d{8}$/',                 // Malta
        'NL' => '/^NL\d{9}B\d{2}$/',           // Netherlands
        'PL' => '/^PL\d{10}$/',                // Poland
        'PT' => '/^PT\d{9}$/',                 // Portugal
        'RO' => '/^RO\d{2,10}$/',              // Romania
        'SE' => '/^SE\d{12}$/',                // Sweden
        'SI' => '/^SI\d{8}$/',                 // Slovenia
        'SK' => '/^SK\d{10}$/',                // Slovakia
        'GB' => '/^GB\d{9}$|^GB\d{12}$|^GBGD\d{3}$|^GBHA\d{3}$/' // United Kingdom
    ];

    const ORDER_DOCUMENT_TYPE_INVOICE = 1;
    const ORDER_DOCUMENT_TYPE_PACKING_SLIP = 2;
    const ORDER_DOCUMENT_TYPE_PROFORMA = 3;
    const ORDER_DOCUMENT_TYPE_CREDIT_NOTE = 4;

    const GB_VAT_MAX_PRICE = 135;

    const CUSTOMER_FEEDBACK = [
        '1' => "Missing item (KF)",
        '2' => "Wrong item (KF)",
        '3' => "Damaged item (KF)",
        '4' => "Broken item (KF)",
        '5' => "Overweight (KF)",
        '6' => "Not specified (KF)",
        '7' => "Lost order (VF)",
        '8' => "Not specified (VF)",
        '9' => "Double order (SF)",
        '10' => "Missing item (SF)",
        '11' => "Revocation",
        '12' => "Damaged item (VF)",
        '13' => "Long delivery",
        '14' => "Not at home",
        '15' => "Wrong address",
    ];

    const REFUND_REASONS = [
        "Returns",
        "Missing Items",
        "Damaged Items",
        "Wrong Items",
        "VAT",
        "Manual Refund to cancel order",
        "Shipping Cost",
        "Items no longer wanted",
        "Other",
    ];

    const USER_SOURCE_WEB = 0;
    const USER_SOURCE_WEB_OMS = 4;
    const USER_SOURCE_AMAZON = 1;
    const USER_SOURCE_AMAZON_OMS = 5;
    const USER_SOURCE_EBAY = 2;
    const USER_SOURCE_OMS = 6;
}
