<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentMethod extends Model
{
    protected $fillable = [
        'type', 'payment_key', 'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function payment_method_translation(): HasOne
    {
        return $this->hasOne(PaymentMethodTranslation::class);
    }

    public function customer_groups_pivot(): HasMany
    {
        return $this->hasMany(PaymentMethodCustomerGroup::class);
    }

    public function currencies_pivot(): HasMany
    {
        return $this->hasMany(PaymentMethodCurrency::class);
    }

    public function countries_pivot(): HasMany
    {
        return $this->hasMany(PaymentMethodCountry::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(PaymentMethodAccount::class);
    }

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'payment_method_currencies');
    }
}
