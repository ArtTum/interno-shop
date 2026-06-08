<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'symbol', 'base', 'rate', 'gbp_rate', 'actual_rate'
    ];

    protected $casts = [
        'base' => 'boolean'
    ];
}
