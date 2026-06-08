<?php

namespace App\Repositories\ShippingZoneCountry\Interfaces;

interface ShippingZoneCountryRepositoryInterface
{
    public function insert(array $data): bool;
    public function deleteByParams(string $whereField, string|int $whereValue): bool;
}
