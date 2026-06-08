<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignEmail extends Model
{
    protected $fillable = [
        'campaign_id', 'language_id', 'content', 'sending_time', 'status'
    ];

    protected $casts = [
        'content' => 'array'
    ];

    public function excluded_segments(): HasMany
    {
        return $this->hasMany(CampaignEmailSegment::class)->where('excluded', true);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(CampaignEmailSegment::class)->where('excluded', false);
    }
}
