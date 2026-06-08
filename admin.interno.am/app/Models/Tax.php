<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 'name', 'state_code', 'zip', 'city', 'rate', 'shipping', 'tax_free'
    ];

    protected $casts = [
        'shipping' => 'boolean',
        'tax_free' => 'boolean',
        'changed' => 'boolean',
        'deleted' => 'boolean'
    ];
}
