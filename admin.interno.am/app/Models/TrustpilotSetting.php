<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrustpilotSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'secret', 'bs_id', 'excellent_text', 'excluded_skus', 'page_id'
    ];

}
