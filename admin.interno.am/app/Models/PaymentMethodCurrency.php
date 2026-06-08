<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodCurrency extends Model
{
    protected $fillable = [
        'payment_method_id', 'currency_id'
    ];
}
