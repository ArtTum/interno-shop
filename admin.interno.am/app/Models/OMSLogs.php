<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OMSLogs extends Model
{
    protected $fillable = [
        'oms_order_id', 'log'
    ];
}
