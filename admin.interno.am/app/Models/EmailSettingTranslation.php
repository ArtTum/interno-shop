<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_setting_id', 'language_id', 'subject', 'title', 'top_text', 'bottom_text', 'footer_text',
        'admin_receiver_email_address', 'attach_document'
    ];

    protected $casts = ['attach_document' => 'boolean'];

    public function email_settings(): BelongsTo
    {
        return $this->belongsTo(EmailSetting::class);
    }
}
