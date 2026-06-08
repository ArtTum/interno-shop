<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'name', 'position_packstation', 'production_price', 'regular_price', 'stock_quantity',
        'negative_stock', 'net_weight', 'gross_weight', 'country', 'hs_code', 'hs_name', 'proper_shipping_name', 'un_numbers', 'package_group',
        'dangerous_goods_class', 'mark_nos', 'packages_type', 'send_in_separate_package',
        'max_items_per_box', 'status', 'stock_status'
    ];

    protected $casts = [
        'negative_stock' => 'boolean',
        'send_in_separate_package' => 'boolean',
        'status' => 'boolean',
        'stock_status' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product_variant_parents(): HasMany
    {
        return $this->hasMany(ProductVariantParent::class);
    }

    public function product_multiselect_parents(): HasMany
    {
        return $this->hasMany(ProductMultiselectOptionParent::class);
    }
}
