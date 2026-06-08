<?php

namespace App\Repositories\OrderRefundItem\Interfaces;

interface OrderRefundItemRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function insert(array $data): bool;
}
