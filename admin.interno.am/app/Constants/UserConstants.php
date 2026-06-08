<?php

namespace App\Constants;

class UserConstants
{
    const USER_GROUPS = [
        'customer' => 'Customer',
        'warehouse' => 'Warehouse',
        'provider' => 'Provider',
        'affiliate' => 'Affiliate member',
    ];

    const USER_SOURCE_WEB = 0;
    const USER_SOURCE_WEB_OMS = 4;
    const USER_SOURCE_AMAZON = 1;
    const USER_SOURCE_AMAZON_OMS = 5;
    const USER_SOURCE_EBAY = 2;
    const USER_SOURCE_OMS = 6;
}
