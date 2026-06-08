<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Variable extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'for_front'
    ];

    protected $casts = [
        'for_front' => 'boolean'
    ];

    public function variable_translation(): HasOne
    {
        return $this->hasOne(VariableTranslation::class);
    }

    public function selected_variable_translation(): HasOne
    {
        return $this->hasOne(VariableTranslation::class);
    }

    public function variable_translations(): HasMany
    {
        return $this->hasMany(VariableTranslation::class);
    }
}
