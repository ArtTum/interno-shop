<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class extendedPrice extends Model
{
    use HasFactory;

    protected $table = 'extended_prices';

    protected $fillable = [
        'disease',
        'price',
        'sale_price',
        'clinic',
        'section',
    ];

    public $timestamps = true;
}
