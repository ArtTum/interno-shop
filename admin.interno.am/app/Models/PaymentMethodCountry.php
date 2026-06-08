<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodCountry extends Model
{
    protected $fillable = [
        'payment_method_id', 'country_id'
    ];
}
