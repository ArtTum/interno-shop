<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name'
    ];

    public function campaign_emails(): HasMany
    {
        return $this->hasMany(CampaignEmail::class);
    }
}
