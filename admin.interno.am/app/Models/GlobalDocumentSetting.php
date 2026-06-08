<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GlobalDocumentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id', 'phone', 'email',
    ];

    public function global_document_setting_translation(): HasOne
    {
        return $this->hasOne(GlobalDocumentSettingTranslation::class);
    }

    public function global_document_setting_translations(): HasMany
    {
        return $this->hasMany(GlobalDocumentSettingTranslation::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
