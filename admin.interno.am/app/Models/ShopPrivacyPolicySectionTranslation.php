<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPrivacyPolicySectionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_privacy_policy_section_id',
        'language_id',
        'title',
        'text',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ShopPrivacyPolicySection::class, 'shop_privacy_policy_section_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
