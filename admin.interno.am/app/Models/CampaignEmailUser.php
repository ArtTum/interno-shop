<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEmailUser extends Model
{
    protected $fillable = [
        'campaign_email_id', 'user_id', 'token', 'excluded', 'status'
    ];

    protected $casts = [
        'excluded' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
