<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEmailSegment extends Model
{
    protected $fillable = [
      'campaign_email_id', 'customer_segment_id', 'excluded'
    ];

    protected $casts = [
        'excluded' => 'boolean'
    ];
}
