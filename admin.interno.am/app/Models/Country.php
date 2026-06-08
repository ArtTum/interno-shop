<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'all_country_id', 'name', 'language_id', 'delivery_days_from', 'delivery_days_to', 'state_required'
    ];

    protected $casts = [
        'state_required' => 'boolean'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function zip_rules(): HasMany
    {
        return $this->hasMany(ZipRule::class);
    }

    public function country_translation(): HasOne
    {
        return $this->hasOne(CountryTranslation::class);
    }

    public function country_translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function state(): HasMany
    {
        return $this->hasMany(CountryState::class);
    }
}
