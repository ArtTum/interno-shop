<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSegment extends Model
{
    protected $fillable = [
        'name', 'criteria', 'cache_hours', 'expire_date', 'in_progress'
    ];

    protected $casts = [
        'criteria' => 'array'
    ];

    public function customer_segment_users(): HasMany
    {
        return $this->hasMany(CustomerSegmentUser::class);
    }

    public function customer_segment_imported_users(): HasMany
    {
        return $this->hasMany(CustomerSegmentUser::class)->where('imported', true);
    }
}
