<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalDocumentSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'global_document_setting_id', 'language_id', 'name', 'phone', 'email', 'address', 'footer_text', 'seller_info', 'rules'
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
