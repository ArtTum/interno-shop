<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_setting_id', 'language_id', 'value'
    ];
}
