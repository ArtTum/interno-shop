<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['searching_key', 'parent_id', 'cart_id', 'product_variant_id', 'quantity', 'gift_card_details'];

    protected $casts = [
        'gift_card_details' => 'array'
    ];

    public function product_variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function cart_extra_products(): HasMany
    {
        return $this->hasMany(CartItem::class, 'parent_id', 'id');
    }
}
