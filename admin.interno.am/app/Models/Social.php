<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Social extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'url', 'icon', 'color', 'status', 'show_in_frontend', 'sort_order'
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_in_frontend' => 'boolean',
    ];

    public function social_translation(): HasOne
    {
        return $this->hasOne(SocialTranslation::class);
    }

    public function social_translations(): HasMany
    {
        return $this->hasMany(SocialTranslation::class);
    }
}
