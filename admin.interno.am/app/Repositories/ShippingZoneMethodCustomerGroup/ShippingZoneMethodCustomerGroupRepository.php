<?php

namespace App\Repositories\ShippingZoneMethodCustomerGroup;

use App\Repositories\BaseRepository;
use App\Models\ShippingZoneMethodCustomerGroup;

class ShippingZoneMethodCustomerGroupRepository extends BaseRepository
{
    public function __construct(ShippingZoneMethodCustomerGroup $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    public function getIdsByMethodId(int $methodId)
    {
        return $this->model->select('customer_group_id')
            ->where('shipping_zone_method_id', $methodId)
            ->pluck('customer_group_id')
            ->toArray();
    }

    public function bulkDeleteByField(int $methodId, array $customerGroupIds): bool
    {
        return $this->model
            ->where('shipping_zone_method_id', $methodId)
            ->whereIn('customer_group_id', $customerGroupIds)
            ->delete();
    }
}
