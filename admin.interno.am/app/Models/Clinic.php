<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';

    protected $fillable = [
        'clinic',
        'name',
        'phone',
        'email',
        'sale',
        'other_sale',
        'address',
        'color',
    ];

    public $timestamps = true;
}
