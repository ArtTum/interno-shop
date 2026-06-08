<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermalinkTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'language_id', 'slug'
    ];
}
