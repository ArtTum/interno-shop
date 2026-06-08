<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'media_id', 'type', 'status', 'is_home', 'priority', 'no_index', 'published_at', 'a_plus_content_type'
    ];

    protected $casts = [
        'status' => 'boolean',
        'no_index' => 'boolean',
        'is_home' => 'boolean',
    ];

    public function customer_groups_pivot(): HasMany
    {
        return $this->hasMany(PageCustomerGroup::class);
    }

    public function user_levels_pivot(): HasMany
    {
        return $this->hasMany(PageUserLevel::class);
    }

    public function page_translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
