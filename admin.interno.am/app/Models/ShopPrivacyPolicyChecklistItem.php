<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopPrivacyPolicyChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_privacy_policy_id',
        'sort_order',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ShopPrivacyPolicy::class, 'shop_privacy_policy_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopPrivacyPolicyChecklistItemTranslation::class);
    }
}
