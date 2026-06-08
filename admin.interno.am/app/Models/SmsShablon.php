<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsShablon extends Model
{
    use HasFactory;

    protected $table = 'sms_shablons';

    protected $fillable = [
        'name',
        'sms_text',
    ];

    public $timestamps = true;
}
