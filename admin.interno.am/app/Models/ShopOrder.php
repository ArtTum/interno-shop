<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOrder extends Model
{
    protected $fillable = [
        'status',
        'language',
        'customer_first_name',
        'customer_last_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_master_code',
        'craftsman_id',
        'craftsman_code',
        'craftsman_name',
        'items',
        'total',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
        'craftsman_id' => 'integer',
    ];

    public function craftsman(): BelongsTo
    {
        return $this->belongsTo(ShopCraftsman::class, 'craftsman_id');
    }

    public function getCustomerAttribute(): array
    {
        return [
            'firstName' => $this->customer_first_name,
            'lastName' => $this->customer_last_name,
            'phone' => $this->customer_phone,
            'email' => $this->customer_email,
            'address' => $this->customer_address,
            'masterCode' => $this->customer_master_code,
        ];
    }

    public function getCraftsmanSnapshotAttribute(): ?array
    {
        if (!$this->craftsman_code && !$this->craftsman_name && !$this->craftsman_id) {
            return null;
        }

        return [
            'id' => $this->craftsman_id,
            'code' => $this->craftsman_code,
            'name' => $this->craftsman_name,
        ];
    }

    public function toOrderArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'language' => $this->language,
            'customer' => $this->customer,
            'craftsman' => $this->craftsman_snapshot,
            'items' => $this->items ?? [],
            'total' => $this->total,
        ];
    }
}
