<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'project', 'area_size', 'start_date', 'full_name', 'country', 'zip_code', 'email', 'phone', 'description',
        'email_sent', 'material', 'craftsman', 'language_id', 'process', 'status_1_date', 'status_2_date'
    ];

    protected $casts = [
        'process' => 'array'
    ];

    public function getProcessAttribute($value)
    {
        return array_replace([
            'status_1' => null,
            'status_1_desc' => null,
            'status_2' => null,
            'status_2_desc' => null,
        ], (array) json_decode($value, true));
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function order_billing_addresses(): HasMany
    {
        return $this->hasMany(OrderBillingAddress::class, 'email', 'email');
    }
}
