<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'code', 'type', 'amount', 'free_shipping', 'expires_at', 'min_spend', 'max_spend', 'exclude_sale_items',
        'usage_limit', 'usage_limit_per_user', 'description', 'status', 'gift_card_details'
    ];

    protected $casts = [
        'gift_card_details' => 'array',
        'free_shipping' => 'boolean',
        'exclude_sale_items' => 'boolean',
        'status' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(CouponProduct::class)->where('is_excluded', false);
    }

    public function excluded_products(): HasMany
    {
        return $this->hasMany(CouponProduct::class)->where('is_excluded', true);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(CouponCategory::class)->where('is_excluded', false);
    }

    public function excluded_categories(): HasMany
    {
        return $this->hasMany(CouponCategory::class)->where('is_excluded', true);
    }

    public function coupon_allowed_emails(): HasMany
    {
        return $this->hasMany(CouponAllowedEmail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
