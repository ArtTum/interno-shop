<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPrivacyPolicyChecklistItemTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_privacy_policy_checklist_item_id',
        'language_id',
        'text',
    ];

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ShopPrivacyPolicyChecklistItem::class, 'shop_privacy_policy_checklist_item_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
