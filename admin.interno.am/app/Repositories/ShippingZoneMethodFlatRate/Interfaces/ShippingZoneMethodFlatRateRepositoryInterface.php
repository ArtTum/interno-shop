<?php

namespace App\Repositories\ShippingZoneMethodFlatRate\Interfaces;

interface ShippingZoneMethodFlatRateRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function bulkDeleteByParams(string $whereField, array $whereValue): bool;
}
