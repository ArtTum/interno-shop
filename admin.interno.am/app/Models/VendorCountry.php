<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCountry extends Model
{
    use HasFactory;

    protected $connection = 'mysql_vendors';

    protected $fillable = [
        'vendor_id', 'all_country_id'
    ];
}
