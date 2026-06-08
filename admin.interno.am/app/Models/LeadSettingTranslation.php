<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_setting_id',
        'language_id',
        'value'
    ];
}
