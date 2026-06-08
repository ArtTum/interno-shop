<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodTranslation extends Model
{
    protected $fillable = [
        'payment_method_id', 'language_id', 'name', 'description', 'priority'
    ];
}
