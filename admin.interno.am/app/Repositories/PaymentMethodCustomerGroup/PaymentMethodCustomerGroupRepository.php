<?php

namespace App\Repositories\PaymentMethodCustomerGroup;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethodCustomerGroup;

class PaymentMethodCustomerGroupRepository extends BaseRepository
{
    public function __construct(PaymentMethodCustomerGroup $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }


    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }


    public function fetchIdsByMethodId(int $methodId): array
    {
        return $this->model->select('customer_group_id')->where('payment_method_id', $methodId)->pluck('customer_group_id')->toArray();
    }

    public function deleteByMethodAndCustomerGroupIds(int $methodId, array $customerGroupIds): bool
    {
        return $this->model->where('payment_method_id', $methodId)->whereIn('customer_group_id', $customerGroupIds)->delete();
    }
}
