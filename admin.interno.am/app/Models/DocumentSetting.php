<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'statuses', 'display_invoice_date', 'display_email_address', 'display_phone_number',
        'generate_on_new_order', 'generate_invoice_also_in_base_language', 'create_automatically_after_refunding',
        'display_credit_note_date', 'use_positive_prices', 'show_original_invoice_number, statuses',
    ];

    protected $casts = [
        'display_email_address' => 'boolean',
        'display_phone_number' => 'boolean',
        'generate_on_new_order' => 'boolean',
        'generate_invoice_also_in_base_language' => 'boolean',
        'create_automatically_after_refunding' => 'boolean',
        'use_positive_prices' => 'boolean',
        'show_original_invoice_number' => 'boolean',
        'statuses' => 'array',
    ];

    public function document_setting_translation(): HasOne
    {
        return $this->hasOne(DocumentSettingTranslation::class);
    }

    public function document_setting_translations(): HasMany
    {
        return $this->hasMany(DocumentSettingTranslation::class);
    }
}
