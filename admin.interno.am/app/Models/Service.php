<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Disease;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'patient_full_name',
        'age',
        'phone',
        'year',
        'month',
        'call_date',
        'next_call_date',
        'disease_id',
        'hospital_id',
        'disease',
        'medical_and_doctor',
        'other_phone',
        'find_aboutus',
        'additional_data',
        'color',
        'month_copy',
        'day_surgery',
        'konsultacia',
        'user_id',
        'change_user_id',
        'info',
        'price',
        'sale_price',
        'sale',
        'a_d',
        'type',
        'copy',
        'incoming_color',
        'departure_datetime',
        'preliminary_price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diseaseRecord(): BelongsTo
    {
        return $this->belongsTo(Disease::class, 'disease_id');
    }

    public function hospitalRecord(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
