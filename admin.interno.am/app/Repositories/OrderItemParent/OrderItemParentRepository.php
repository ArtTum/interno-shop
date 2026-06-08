<?php

namespace App\Repositories\OrderItemParent;

use App\Constants\OrderConstants;
use App\Repositories\BaseRepository;
use App\Models\OrderItemParent;
use Illuminate\Support\Facades\DB;

class OrderItemParentRepository extends BaseRepository
{
   public function __construct(OrderItemParent $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function getItemsBySkuAndOrderId(string|int $sku, int $orderId)
    {
        return $this->model->select('id', 'quantity', 'sku', 'order_item_id')
            ->where('sku', $sku)
            ->whereHas('order_item', function ($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->orderBy('quantity', 'desc')
            ->get();
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function bulkDeleteByField(string $field, array $data): bool
    {
        return $this->model->where($field, $data)->delete();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }



    public function getItemInfoBaseQuery(array $data, array $billingCountryCodes, array $shippingCountryCodes, ?string $dateType)
    {
        return $this->model ->join('order_items', 'order_items.id', '=', 'order_item_parents.order_item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('items', 'items.id', '=', 'order_item_parents.item_id')
            ->where('items.id', $data['item_id'])
            ->whereIn('orders.status', [
                OrderConstants::STATUS_PROCESSING,
                OrderConstants::STATUS_COMPLETED,
            ])
            ->whereNull('orders.full_reshipment')
            ->when($dateType !== null, function ($query) use ($data, $dateType) {
                $query->where('orders.created_at', '>=', $data["{$dateType}order_date_from"])
                    ->where('orders.created_at', '<=', to_date_at_the_end($data["{$dateType}order_date_to"]));
            })
            ->when(!empty($billingCountryCodes), function ($query) use ($billingCountryCodes) {
                $query->whereIn('orders.id', function ($sub) use ($billingCountryCodes) {
                    $sub->select('order_id')
                        ->from('order_billing_addresses')
                        ->whereIn('country_code', $billingCountryCodes);
                });
            })
            ->when(!empty($shippingCountryCodes), function ($query) use ($shippingCountryCodes) {
                $query->whereIn('orders.id', function ($sub) use ($shippingCountryCodes) {
                    $sub->select('order_id')
                        ->from('order_shipping_addresses')
                        ->whereIn('country_code', $shippingCountryCodes);
                });
            });
    }

    public function getInfoForItemAnalyticsByDaily(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType)
    {
        return $this->getItemInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, $dateType)
            ->selectRaw('DATE(orders.created_at) AS order_date, SUM(order_item_parents.quantity) AS total_sold')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('order_date', 'ASC')
            ->get()
            ->keyBy('order_date');
    }
}
