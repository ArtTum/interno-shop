<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethodAccount extends Model
{
    protected $fillable = [
        'payment_method_id', 'country_id', 'currency_id', 'info', 'is_base'
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'info' => 'array',
    ];

    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
