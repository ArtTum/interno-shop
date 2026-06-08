<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'calculator_id', 'language_id', 'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }
}
