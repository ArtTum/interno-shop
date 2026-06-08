<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroupIP extends Model
{
    protected $table = 'user_group_ips';

    protected $fillable = [
        'user_group_id', 'ip', 'expires_at'
    ];
}
