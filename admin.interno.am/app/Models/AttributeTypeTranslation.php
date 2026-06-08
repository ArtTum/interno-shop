<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeTypeTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_type_id', 'language_id', 'slug', 'name', 'label', 'description', 'approved'
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];
}
