<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'status', 'total_lines', 'invalid_lines', 'succeed_lines', 'uploaded_by', 'user_id'
    ];

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function upload_logs(): HasMany
    {
        return $this->hasMany(UploadLog::class);
    }
}
