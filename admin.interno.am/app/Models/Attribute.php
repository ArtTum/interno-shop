<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'priority', 'attribute_type_id', 'media_id', 'color_code'
    ];

    public function attribute_type(): BelongsTo
    {
        return $this->belongsTo(AttributeType::class);
    }

    public function attribute_translation(): HasOne
    {
        return $this->hasOne(AttributeTranslation::class);
    }

    public function product_attribute(): HasOne
    {
        return $this->hasOne(ProductAttribute::class);
    }

    public function attribute_translations(): HasMany
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function attribute_variants_pivot(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function product_attributes_pivot(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function product_variant(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes');
    }
}
