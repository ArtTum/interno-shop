<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpeditionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_order_weight', 'rules'
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
