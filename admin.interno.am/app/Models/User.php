<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'user_group_id', 'gtin', 'name', 'last_name', 'email', 'gtin', 'password', 'balance',
        'wc_password', 'blocked', 'superadmin', 'newsletter_subscribed', 'ip', 'ip_expires_at', 'subscriber_id',
        'check_client_certificate', 'source'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'blocked' => 'boolean',
        'superadmin' => 'boolean',
        'newsletter_subscribed' => 'boolean',
        'check_client_certificate' => 'boolean',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed'
        ];
    }

    public function user_billing_address(): HasOne
    {
        return $this->hasOne(UserBillingAddress::class);
    }

    public function user_affiliate(): HasOne
    {
        return $this->hasOne(UserAffiliate::class);
    }

    public function user_shipping_address(): HasOne
    {
        return $this->hasOne(UserShippingAddress::class);
    }

    public function shared_carts(): HasMany
    {
        return $this->hasMany(SharedCart::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->whereNull('full_reshipment');
    }

    public function order_statistic(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function customer_segment_users(): HasMany
    {
        return $this->hasMany(CustomerSegmentUser::class);
    }

    public function user_group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }
}
