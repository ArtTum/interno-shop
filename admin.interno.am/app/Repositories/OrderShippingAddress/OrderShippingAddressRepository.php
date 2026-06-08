<?php

namespace App\Repositories\OrderShippingAddress;

use App\Models\OrderShippingAddress;
use App\Repositories\BaseRepository;
use App\Repositories\OrderShippingAddress\Interfaces\OrderShippingAddressRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class OrderShippingAddressRepository extends BaseRepository implements OrderShippingAddressRepositoryInterface
{
    public function __construct(OrderShippingAddress $model)
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

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }

    public function existsByOrderId(int $orderId): bool
    {
        return $this->model->where('order_id', $orderId)->exists();
    }
}
