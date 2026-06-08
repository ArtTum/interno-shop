<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OutlookSetting extends Model
{
    use HasFactory;

    protected $connection = 'mysql_vendors';

    protected $fillable = [
        'microsoft_access_token', 'microsoft_refresh_token', 'microsoft_token_expires_at', 'oauth_state'
    ];

}
