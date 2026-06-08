<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name',
    ];

    protected $casts = [];

    public function email_setting_translation(): HasOne
    {
        return $this->hasOne(EmailSettingTranslation::class);
    }

    public function email_setting_translations(): HasMany
    {
        return $this->hasMany(EmailSettingTranslation::class);
    }
}
