<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOption extends Model
{
    protected $connection = 'mysql_vendors';

    protected $fillable = [
        'vendor_id',
        'b2b',
        'loyalty_programs',
        'shipping_and_labels',
        'accounting_features',
        'leads',
        'abandoned_cart_emails',
        'newsletter_system',
        'dgd',
        'cookie_management',
        'marketplaces',
    ];


    protected $casts = [
        'shipping_and_labels' => 'array',
        'marketplaces' => 'array',
        'loyalty_programs' => 'boolean',
        'accounting_features' => 'boolean',
        'leads' => 'boolean',
        'abandoned_cart_emails' => 'boolean',
        'newsletter_system' => 'boolean',
        'dgd' => 'boolean',
        'cookie_management' => 'boolean',
    ];
}
