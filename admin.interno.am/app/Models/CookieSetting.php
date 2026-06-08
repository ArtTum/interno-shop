<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookieSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label'
    ];

    public function cookie_setting_translation()
    {
        return $this->hasOne(CookieSettingTranslation::class);
    }
}
