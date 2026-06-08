<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ZipRule extends Model
{
    protected $fillable = [
        'country_id', 'fee', 'zips'
    ];

    public function zip_rule_translation(): HasOne
    {
        return $this->hasOne(ZipRuleTranslation::class);
    }

    public function zip_rule_translations(): HasMany
    {
        return $this->hasMany(ZipRuleTranslation::class);
    }
}
