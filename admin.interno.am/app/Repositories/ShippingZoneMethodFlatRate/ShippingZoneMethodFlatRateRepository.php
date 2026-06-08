<?php

namespace App\Repositories\ShippingZoneMethodFlatRate;

use App\Models\ShippingZoneMethodFlatRate;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingZoneMethodFlatRate\Interfaces\ShippingZoneMethodFlatRateRepositoryInterface;

class ShippingZoneMethodFlatRateRepository extends BaseRepository implements ShippingZoneMethodFlatRateRepositoryInterface
{
    public function __construct(ShippingZoneMethodFlatRate $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function bulkDeleteByParams(string $whereField, array $whereValue): bool
    {
        return $this->model->whereIn($whereField, $whereValue)->delete();
    }
}
