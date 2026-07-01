<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'media_id',
        'image',
        'gallery',
        'gallery_media_ids',
        'options',
        'option_code',
        'option_size',
        'option_quantity',
        'option_type_id',
        'option_unit',
        'option_piece',
        'option_height',
        'option_material',
        'option_color_id',
        'option_color_ids',
        'is_new',
        'is_temporarily_unavailable',
        'purchase_quantity_limited',
        'purchase_quantity_limit',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'gallery' => 'array',
        'gallery_media_ids' => 'array',
        'options' => 'array',
        'option_color_ids' => 'array',
        'is_new' => 'boolean',
        'is_temporarily_unavailable' => 'boolean',
        'purchase_quantity_limited' => 'boolean',
        'purchase_quantity_limit' => 'integer',
        'status' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function optionType(): BelongsTo
    {
        return $this->belongsTo(ShopProductOptionType::class, 'option_type_id');
    }

    public function optionColor(): BelongsTo
    {
        return $this->belongsTo(ShopProductColor::class, 'option_color_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopProductTranslation::class);
    }

    public function attributePrices(): HasMany
    {
        return $this->hasMany(ShopProductAttributePrice::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'shop_product_related_products',
            'shop_product_id',
            'related_shop_product_id'
        )
            ->withPivot('sort_order')
            ->orderBy('shop_product_related_products.sort_order')
            ->orderBy('shop_products.id');
    }
}
