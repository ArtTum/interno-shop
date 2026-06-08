<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TntConsignmentNoteNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignment_note_number', 'used', 'order_number', 'used_date',
    ];

    protected $casts = [
        'used' => 'boolean',
        'used_date' => 'datetime',
    ];

    public function getUsedDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y H:i') : null;
    }
}
