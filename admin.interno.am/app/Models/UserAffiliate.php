<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAffiliate extends Model
{
    protected $fillable = [
        'user_id', 'language_id', 'member_group_id', 'socials', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'socials' => 'array',
    ];

    public function member_group(): BelongsTo
    {
        return $this->belongsTo(MemberGroup::class);
    }
}
