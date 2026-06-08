<?php

namespace App\Repositories\ShippingZoneCountry;

use App\Models\ShippingZoneCountry;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingZoneCountry\Interfaces\ShippingZoneCountryRepositoryInterface;

class ShippingZoneCountryRepository extends BaseRepository implements ShippingZoneCountryRepositoryInterface
{
    public function __construct(ShippingZoneCountry $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByParams(string $whereField, string|int $whereValue): bool
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }

    public function getShippingZoneIdsByCountryId(string $countryId): ?array
    {
        return $this->model->select('shipping_zone_id')->where('country_id', $countryId)->pluck('shipping_zone_id')->toArray();
    }
}
