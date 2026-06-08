<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsBaza extends Model
{
    protected $table = 'sms_baza';

    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'call_date',
        'phone',
        'other_phone',
        'sms_bazacol',
        'patient_full_name',
        'disease',
        'medical_and_doctor',
    ];

    public $timestamps = true;
}
