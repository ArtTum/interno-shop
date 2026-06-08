<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_level_id',
        'access_purchase_receipts',
        'access_premium_content',
        'free_shipping',
        'workshops_seminars',
        '30_day_return',
        'same_day_shipping',
        'express_delivery',
        'access_product_shortages',
        'priority_support',
        'invoice_payment',
        'invitations_events',
        'access_new_products',
        'free_product_samples',
    ];

    protected $casts = [
        'access_purchase_receipts' => 'boolean',
        'access_premium_content' => 'boolean',
        'free_shipping' => 'boolean',
        'workshops_seminars' => 'boolean',
        '30_day_return' => 'boolean',
        'same_day_shipping' => 'boolean',
        'express_delivery' => 'boolean',
        'access_product_shortages' => 'boolean',
        'priority_support' => 'boolean',
        'invoice_payment' => 'boolean',
        'invitations_events' => 'boolean',
        'access_new_products' => 'boolean',
        'free_product_samples' => 'boolean',
    ];
}
