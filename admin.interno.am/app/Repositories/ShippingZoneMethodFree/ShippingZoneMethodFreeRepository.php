<?php

namespace App\Repositories\ShippingZoneMethodFree;

use App\Models\ShippingZoneMethodFree;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingZoneMethodFree\Interfaces\ShippingZoneMethodFreeRepositoryInterface;

class ShippingZoneMethodFreeRepository extends BaseRepository implements ShippingZoneMethodFreeRepositoryInterface
{
    public function __construct(ShippingZoneMethodFree $model)
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
