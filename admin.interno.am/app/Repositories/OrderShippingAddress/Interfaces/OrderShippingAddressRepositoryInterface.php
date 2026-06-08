<?php

namespace App\Repositories\OrderShippingAddress\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OrderShippingAddressRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
