<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    use HasFactory;

    protected $table = 'upload_logs';

    protected $fillable = [
        'upload_id', 'log'
    ];
}
