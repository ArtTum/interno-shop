<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'is_visible', 'array_value', 'group'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'array_value' => 'array'
    ];

    public function general_setting_translation(): HasOne
    {
        return $this->hasOne(GeneralSettingTranslation::class);
    }

    public function general_setting_translations(): HasMany
    {
        return $this->hasMany(GeneralSettingTranslation::class);
    }

    public static function needIPChecker(): bool
    {
        return self::where('key', 'use_ip_checker')->value('value') === 'yes';
    }
}
