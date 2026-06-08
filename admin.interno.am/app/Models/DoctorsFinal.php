<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DoctorsFinal extends Model
{
    protected $table = 'doctors_final';

    use HasFactory;

    protected $fillable = [
        'full_name',
        'profession',
        'degree',
        'workplace',
        'other_info',
    ];
}
