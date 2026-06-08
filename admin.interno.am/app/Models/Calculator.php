<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Calculator extends Model
{
    use HasFactory;

    protected $fillable = [
        'status'
    ];

    public function calculator_translation(): HasOne
    {
        return $this->hasOne(CalculatorTranslation::class);
    }
}
