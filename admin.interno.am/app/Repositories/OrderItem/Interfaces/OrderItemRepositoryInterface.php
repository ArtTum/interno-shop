<?php

namespace App\Repositories\OrderItem\Interfaces;

interface OrderItemRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function insert(array $data): bool;
}
