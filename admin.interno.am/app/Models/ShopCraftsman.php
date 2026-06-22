<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopCraftsman extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'code',
        'first_name',
        'last_name',
        'phone',
        'work_region',
        'work_city',
        'work_field',
        'has_whatsapp',
        'has_viber',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'media_id' => 'integer',
        'has_whatsapp' => 'boolean',
        'has_viber' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
