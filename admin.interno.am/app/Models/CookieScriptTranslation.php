<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookieScriptTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cookie_script_id',
        'language_id',
        'name',
        'description',
        'code',
        'granted_anyway',
        'consent_mode_v2',
        'required_cookie',
    ];
}
