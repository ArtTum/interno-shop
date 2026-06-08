<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id', 'category_id', 'is_excluded'
    ];

    protected $casts = [
        'is_excluded' => 'boolean'
    ];
}
