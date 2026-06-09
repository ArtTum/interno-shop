<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPrivacyPolicyTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_privacy_policy_id',
        'language_id',
        'kicker',
        'title',
        'intro',
        'badge_title',
        'badge_text',
        'summary_label',
        'summary_title',
        'summary_text',
        'updated_label',
        'summary_aria',
        'checklist_aria',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ShopPrivacyPolicy::class, 'shop_privacy_policy_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
