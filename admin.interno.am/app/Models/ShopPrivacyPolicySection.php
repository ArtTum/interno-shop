<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopPrivacyPolicySection extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_privacy_policy_id',
        'section_index',
        'icon',
        'is_wide',
        'sort_order',
    ];

    protected $casts = [
        'is_wide' => 'boolean',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ShopPrivacyPolicy::class, 'shop_privacy_policy_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopPrivacyPolicySectionTranslation::class);
    }
}
