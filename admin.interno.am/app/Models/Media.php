<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'original_path',
        'path',
        'title',
        'alt',
        'file_size',
        'width',
        'height',
        'file_type',
        'type',
    ];

    public function media_translation(): HasOne
    {
        return $this->hasOne(MediaTranslation::class);
    }
}
