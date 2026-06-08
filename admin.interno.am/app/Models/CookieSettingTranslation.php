<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookieSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cookie_setting_id',
        'language_id',
        'value'
    ];
}
