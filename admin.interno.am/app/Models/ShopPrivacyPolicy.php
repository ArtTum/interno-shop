<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopPrivacyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'updated_at_label',
        'status',
    ];

    protected $casts = [
        'content' => 'array',
        'status' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ShopPrivacyPolicyTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ShopPrivacyPolicyTranslation::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ShopPrivacyPolicyChecklistItem::class)->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ShopPrivacyPolicySection::class)->orderBy('sort_order');
    }
}
