<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateProgram extends Model
{
    protected $fillable = [
        'name', 'status', 'settings'
    ];

    protected $casts = [
        'status' => 'boolean',
        'settings' => 'array',
    ];
}
