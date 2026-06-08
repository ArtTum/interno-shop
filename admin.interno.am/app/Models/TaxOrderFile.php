<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxOrderFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'path',
    ];

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }
}
