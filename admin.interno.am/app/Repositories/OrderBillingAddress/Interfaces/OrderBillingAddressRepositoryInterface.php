<?php

namespace App\Repositories\OrderBillingAddress\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OrderBillingAddressRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
