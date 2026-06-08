<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'is_sandbox', 'region', 'client_id', 'client_secret', 'dev_id', 'access_token',
        'access_token_expires_at', 'refresh_token', 'refresh_token_expires_at', 'token_type', 'scope'
    ];

    protected $casts = [
        'is_sandbox' => 'boolean'
    ];
}
