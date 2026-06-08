<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_setting_id', 'language_id', 'document_title', 'number_format_prefix', 'text_below_title'
    ];
}
