<?php

namespace App\Repositories\ShippingZoneMethodUserLevel;

use App\Repositories\BaseRepository;
use App\Models\ShippingZoneMethodUserLevel;

class ShippingZoneMethodUserLevelRepository extends BaseRepository
{
   public function __construct(ShippingZoneMethodUserLevel $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    public function getIdsByMethodId(int $methodId)
    {
        return $this->model->select('user_level_id')
            ->where('shipping_zone_method_id', $methodId)
            ->where('excluded', false)
            ->pluck('user_level_id')
            ->toArray();
    }

    public function bulkDeleteByField(int $methodId, array $customerGroupIds): bool
    {
        return $this->model
            ->where('shipping_zone_method_id', $methodId)
            ->where('excluded', false)
            ->whereIn('user_level_id', $customerGroupIds)
            ->delete();
    }

    public function getHideIdsByMethodId(int $methodId)
    {
        return $this->model->select('user_level_id')
            ->where('shipping_zone_method_id', $methodId)
            ->where('excluded', true)
            ->pluck('user_level_id')
            ->toArray();
    }

    public function bulkDeleteByHideField(int $methodId, array $customerGroupIds): bool
    {
        return $this->model
            ->where('shipping_zone_method_id', $methodId)
            ->where('excluded', true)
            ->whereIn('user_level_id', $customerGroupIds)
            ->delete();
    }
}
