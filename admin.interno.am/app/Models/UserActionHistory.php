<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActionHistory extends Model
{
    protected $table = 'user_action_history';

    protected $fillable = [
        'user_id', 'author', 'type', 'description', 'updating_field', 'updating_value'
    ];
}
