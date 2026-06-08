<?php

namespace App\Repositories\ShippingZoneMethodFree\Interfaces;

interface ShippingZoneMethodFreeRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function bulkDeleteByParams(string $whereField, array $whereValue): bool;
}
