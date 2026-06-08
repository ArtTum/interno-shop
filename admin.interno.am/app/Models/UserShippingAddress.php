<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_id', 'name', 'last_name', 'company', 'address', 'address_2', 'city', 'zip', 'state'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
