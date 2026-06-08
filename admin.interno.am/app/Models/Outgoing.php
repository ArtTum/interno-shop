<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outgoing extends Model
{
    protected $table = 'outgoing';

    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'date',
        'info',
        'price',
    ];
}
