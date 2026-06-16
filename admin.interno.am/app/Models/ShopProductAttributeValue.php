<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProductAttributeValue extends Model
{
    use HasFactory;

    public const TYPES = ['height', 'unit', 'size', 'power'];

    protected $fillable = [
        'type',
        'name',
        'value',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
