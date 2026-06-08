<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsHistory extends Model
{
    protected $table = 'sms_history';

    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'sms_date',
        'phone',
        'sms_text',
    ];
}
