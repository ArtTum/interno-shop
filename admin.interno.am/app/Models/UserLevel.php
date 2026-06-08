<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'spent',
        'cashback',
        'discount',
        'media_id',
    ];

    public function user_level_translations(): HasMany
    {
        return $this->hasMany(UserLevelTranslation::class);
    }

    public function user_level_translation(): HasOne
    {
        return $this->hasOne(UserLevelTranslation::class);
    }

    public function options(): HasOne
    {
        return $this->hasOne(UserLevelOption::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
