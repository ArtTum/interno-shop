<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSegmentUser extends Model
{
    protected $fillable = [
        'customer_segment_id', 'user_id', 'imported'
    ];

    protected $casts = [
        'imported' => 'boolean'
    ];
}
