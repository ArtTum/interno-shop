<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AbandonedEmail extends Model
{
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'country',
        'cart_id',
        'locale',
        'language_id',
        'synced_to_newsletter'
    ];

    protected $casts = [
        'synced_to_newsletter' => 'boolean',
    ];

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
