<?php

namespace App\Repositories\OrderInfo;

use App\Constants\OrderConstants;
use App\Repositories\BaseRepository;
use App\Models\OrderInfo;
use Illuminate\Database\Eloquent\Model;

class OrderInfoRepository extends BaseRepository
{
   public function __construct(OrderInfo $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function updateWarehouseStatusByIds(array $ids, int $status)
    {
        return $this->model
            ->whereIn('order_id', $ids)
            ->where('warehouse_status', '!=', OrderConstants::WAREHOUSE_STATUSES['Completed'])
            ->update(['warehouse_status' => $status]);
    }

    public function updateWarehouseStatusById(int $id, int $status)
    {
        return $this->model
            ->where('order_id', $id)
            ->update(['warehouse_status' => $status]);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function bulkUpdateByParams(string $whereField, array $whereValues, array $updateArray)
    {
        return $this->model->whereIn($whereField, $whereValues)->update($updateArray);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }

    public function fetchForCartShareChecking()
    {
        return $this->model->select('order_infos.id as order_infos_id', 'shared_cart_id', 'orders.total_price', 'order_currency_rate',
            'coupon_amount', 'coupon_type', 'total_price', 'total_discount_price', 'orders.id as order_id')
            ->join('orders', 'orders.id', 'order_infos.order_id')
            ->whereNotNull('shared_cart_id')
            ->whereNull('agent_revenue')
            ->where('orders.status', OrderConstants::STATUS_COMPLETED)
            ->get()
            ->toArray();
    }
}
