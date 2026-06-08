<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label'
    ];

    public function lead_setting_translation()
    {
        return $this->hasOne(LeadSettingTranslation::class);
    }
    public function lead_setting_translations()
    {
        return $this->hasMany(LeadSettingTranslation::class);
    }


}
