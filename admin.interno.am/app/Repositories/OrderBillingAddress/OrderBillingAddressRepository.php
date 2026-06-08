<?php

namespace App\Repositories\OrderBillingAddress;

use App\Models\OrderBillingAddress;
use App\Repositories\BaseRepository;
use App\Repositories\OrderBillingAddress\Interfaces\OrderBillingAddressRepositoryInterface;

class OrderBillingAddressRepository extends BaseRepository implements OrderBillingAddressRepositoryInterface
{
    public function __construct(OrderBillingAddress $model)
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

    public function getCountriesByDistinct()
    {
        return $this->model->select('country_code')
            ->distinct()
            ->pluck('country_code')
            ->toArray();
    }

    public function checkOrdersCount(string $email)
    {
        return $this->model->select('id')
            ->where('email', $email)
            ->count();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
