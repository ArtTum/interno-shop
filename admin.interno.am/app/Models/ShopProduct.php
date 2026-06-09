<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'shop_category_id',
        'slug',
        'price',
        'kind',
        'image',
        'gallery',
        'options',
        'is_new',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'gallery' => 'array',
        'options' => 'array',
        'is_new' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopProductTranslation::class);
    }
}
