<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'completed', 'shared_cart_id', 'offer_id'];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function cart_items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function shared_cart(): HasOne
    {
        return $this->hasOne(SharedCart::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
