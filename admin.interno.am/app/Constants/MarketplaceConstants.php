<?php

namespace App\Constants;

class MarketplaceConstants
{
    const AMAZON = 1;
    const EBAY = 2;

    const REGION_USA = 1;
    const REGION_EU = 2;

    const MARKETPLACES = [
        'amazon' => self::AMAZON,
        'ebay' => self::EBAY,
    ];

    const ALL_REGIONS = [
        [
            'id' => self::REGION_USA,
            'name' => 'USA',
        ],
        [
            'id' => self::REGION_EU,
            'name' => 'Europe',
        ],
    ];

    const ALL_MARKETPLACES = [
        [
            'id' => self::AMAZON,
            'name' => 'Amazon',
        ],
        [
            'id' => self::EBAY,
            'name' => 'Ebay',
        ],
    ];

    const ALL_MARKETPLACES_IDS = [
//        'A2EUQ1WTGCTBG2' => 'CA', // Canada
//        'ATVPDKIKX0DER'  => 'US', // USA
//        'A1AM78C64UM0Y8' => 'MX', // Mexico
//        'A2Q3Y263D00KWC' => 'BR', // Brazil

        // Europe
//        'A28R8C7NBKEWEA' => 'IE', // Ireland
//        'A1RKKUPIHCS9HS' => 'ES', // Spain
//        'A1F83G8C2ARO7P' => 'UK', // United Kingdom
//        'A13V1IB3VIYZZH' => 'FR', // France
//        'AMEN7PMS3EDWL'  => 'BE', // Belgium
//        'A1805IZSGTT6HS' => 'NL', // Netherlands
        'A1PA6795UKMFR9' => 'DE', // Germany
//        'APJ6JRA9NG5V4'  => 'IT', // Italy
//        'A2NODRKZP88ZB9' => 'SE', // Sweden
//        'AE08WJ6YKNBMC'  => 'ZA', // South Africa
//        'A1C3SOZRARQ6R3' => 'PL', // Poland
//        'ARBP9OOSHTCHU'  => 'EG', // Egypt
//        'A33AVAJ2PDY3EV' => 'TR', // Turkey
//        'A17E79C6D8DWNP' => 'SA', // Saudi Arabia
//        'A2VIGQ35RCS4UG' => 'AE', // United Arab Emirates
//        'A21TJRUUN4KGV'  => 'IN', // India
    ];
}
