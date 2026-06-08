<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_level_id',
        'language_id',
        'name',
        'description'
    ];
}
