<?php

namespace App\Constants;

class ProductConstants
{
    const TYPES = [
        'simple' => 0,
        'variable' => 1,
        'bundle' => 2,
        'gift_card' => 3,
        'with_multiselect' => 4,
    ];

    const STOCK_STATUS = [
        'in_stock' => 1,
        'out_of_stock' => 0
    ];

    const GALLERY_TYPE = [
        'image' => 0,
        'video' => 1,
        'file' => 2
    ];

    const RELATED_PRODUCT_TYPES = [
        'upsells' => 0,
        'cross_sells' => 1,
        'related' => 2,
        'extra_products' => 3,
        'bundling' => 4,
    ];

    const BUNDLING_OPTIONS = [
        0 => 'dropdown',
        1 => 'radio_with_images',
        2 => 'list',
    ];

    const REVIEWS_LIMIT_FOR_RENDERING = 6;

    const PRODUCT_SOURCE_WEB = 0;
    const PRODUCT_SOURCE_WEB_OMS = 4;
    const PRODUCT_SOURCE_AMAZON = 1;
    const PRODUCT_SOURCE_AMAZON_OMS = 5;
    const PRODUCT_SOURCE_EBAY = 2;
    const PRODUCT_SOURCE_OMS = 6;
}
