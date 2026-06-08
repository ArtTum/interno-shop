<?php

namespace App\Constants;

class AttributeConstants
{
    const DEFAULT_SORT_ORDERS = [
        'id' => 0,
        'value' => 1,
        'priority' => 2
    ];

    const TYPES = [
        'dropdown' => 0,
        'radio' => 1,
    ];

    const ATTRIBUTE_LOGIC = [
        'variation' => 0,
        'simple' => 1
    ];
}
