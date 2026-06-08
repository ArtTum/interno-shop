<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AllCountry extends Model
{
    use HasFactory;

    protected $connection = 'mysql_vendors';

    protected $fillable = [
        'code', 'name', 'icon', 'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function vendor_country(): HasOne
    {
        return $this->hasOne(VendorCountry::class);
    }

    public function vendor_checkout_country(): HasOne
    {
        return $this->hasOne(VendorCheckoutCountry::class);
    }
}
