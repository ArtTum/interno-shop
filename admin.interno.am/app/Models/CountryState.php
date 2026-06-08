<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CountryState extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 'code', 'name'
    ];

    public function country_state_translation(): HasOne
    {
        return $this->hasOne(CountryStateTranslation::class);
    }

    public function country_state_translations(): HasMany
    {
        return $this->hasMany(CountryStateTranslation::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
