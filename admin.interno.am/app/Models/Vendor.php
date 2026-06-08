<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vendor extends Model
{
    use HasFactory;

    protected $connection = 'mysql_vendors';

    protected $fillable = [
        'name', 'db_name', 'db_server_ip', 'status', 'domain'
    ];

    public function checkout_countries(): BelongsToMany
    {
        return $this->belongsToMany(AllCountry::class, 'vendor_checkout_countries');
    }
}
