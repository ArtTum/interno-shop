<?php

namespace App\Constants;

class ReviewConstants
{
    const TYPE_IMAGE = 0;
    const TYPE_VIDEO = 1;

    const TYPES = [
        'video' => self::TYPE_VIDEO,
        'image' => self::TYPE_IMAGE,
    ];
}
