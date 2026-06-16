<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProductAttributePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_product_id',
        'shop_product_attribute_value_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'shop_product_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(ShopProductAttributeValue::class, 'shop_product_attribute_value_id');
    }
}
