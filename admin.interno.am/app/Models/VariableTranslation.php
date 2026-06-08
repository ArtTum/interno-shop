<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'variable_id', 'language_id', 'value'
    ];

}
