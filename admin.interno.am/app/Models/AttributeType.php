<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeType extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'priority', 'default_sort_order', 'type', 'logic', 'is_filterable', 'is_conditional', 'bigger_values'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_conditional' => 'boolean',
        'bigger_values' => 'boolean',
    ];

    public function attribute_type_translation(): HasOne
    {
        return $this->hasOne(AttributeTypeTranslation::class);
    }

    public function attribute_type_translations(): HasMany
    {
        return $this->hasMany(AttributeTypeTranslation::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }
}
