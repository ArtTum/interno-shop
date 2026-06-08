<?php

namespace App\Repositories\ShippingLabelSettingCountry\Interfaces;

interface ShippingLabelSettingCountryRepositoryInterface
{
    public function insert(array $data): bool;
    public function deleteByParams(string $whereField, string|int $whereValue): bool;
}
