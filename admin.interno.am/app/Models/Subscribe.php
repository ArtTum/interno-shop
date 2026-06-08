<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribe extends Model
{
    protected $table = 'subscribe';

    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'date',
        'phone',
        'full_name',
        'doctor',
        'description',
        'hospital',
        'status',
        'color',
    ];
}
