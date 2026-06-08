<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CookieScripts extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
    ];

    public function cookie_script_translations(): HasMany
    {
        return $this->hasMany(CookieScriptTranslation::class, 'cookie_script_id', 'id');
    }

    public function cookie_script_translation(): HasOne
    {
        return $this->hasOne(CookieScriptTranslation::class);
    }
}
