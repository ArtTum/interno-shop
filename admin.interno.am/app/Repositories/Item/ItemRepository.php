<?php

namespace App\Repositories\Item;

use App\Constants\OrderConstants;
use App\Models\Item;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ItemRepository extends BaseRepository
{
    public function __construct(Item $model)
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

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with([
                'category' => function ($query) use ($params) {
                    $query->select('categories.id', 'name')
                        ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                        ->where('category_translations.language_id', '=', $params['base_language_id']);
                }
            ])
            ->withCount(['product_variant_parents' => function ($query) use ($params) {
                $query->whereHas('product_variant', function ($query) use ($params) {
                    $query->whereHas('product');
                });
            }])
            ->withCount(['product_multiselect_parents' => function ($query) use ($params) {
                $query->whereHas('product_multiselect_option', function ($query) use ($params) {
                    $query->whereHas('product_multiselect', function ($query) use ($params) {
                        $query->whereHas('product');
                    });
                });
            }])
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
            $query->whereIn('id', $data['ids'])
                ->orderBy($data['ordering_field'], $data['ordering_direction']);;
        })
            ->get();
    }

    private function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function getIdBySKU(string $sku)
    {
        return $this->model->select('id')->where('sku', $sku)->value('id');
    }

    public function fetchAsParam()
    {
        return $this->model->select('id as value', 'sku as label')->get()->toArray();
    }

    public function fetchDataForPartialRefund(array $ids)
    {
        return $this->model->select('id', 'sku', 'name', 'net_weight', 'gross_weight', 'un_numbers', 'production_price', 'category_id')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function getForOrderWarehouseAlternatives(array $skus)
    {
        return $this->model->select('id', 'category_id', 'sku', 'name', 'production_price', 'regular_price')
            ->whereIn('sku', $skus)
            ->get()
            ->keyBy('sku');
    }

    public function getSkuById(int $itemId)
    {
        return $this->model->select('sku')
            ->where('id', $itemId)
            ->first();
    }

    public function fetchForAnalytics(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType)
    {
        return $this->model->selectRaw(
            'items.name as item_name,
        items.sku as item_sku,
        COALESCE(order_stats.total_sold, 0) AS total_sold'
        )
            ->leftJoinSub(
                DB::table('order_item_parents')
                    ->selectRaw(
                        'order_item_parents.item_id,
                SUM(order_item_parents.quantity) AS total_sold'
                    )
                    ->join('order_items', 'order_items.id', '=', 'order_item_parents.order_item_id')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('order_billing_addresses', 'orders.id', '=', 'order_billing_addresses.order_id')
                    ->join('order_shipping_addresses', 'orders.id', '=', 'order_shipping_addresses.order_id')
                    ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->whereNull('orders.full_reshipment')
                    ->whereBetween('orders.created_at', [
                        $data["{$dateType}order_date_from"],
                        to_date_at_the_end($data["{$dateType}order_date_to"])
                    ])
                    ->when(!empty($billingCountryCodes), function ($query) use ($billingCountryCodes) {
                        $query->whereIn('order_billing_addresses.country_code', $billingCountryCodes);
                    })
                    ->when(!empty($shippingCountryCodes), function ($query) use ($shippingCountryCodes) {
                        $query->whereIn('order_shipping_addresses.country_code', $shippingCountryCodes);
                    })
                    ->groupBy('order_item_parents.item_id'),
                'order_stats',
                'items.id',
                '=',
                'order_stats.item_id'
            )
            ->orderBy('total_sold', 'desc')
            ->get()
            ->toArray();
    }

    public function autocomplete(string $field, ?string $searchTerm, array $alreadySelectIds, array $excludedIds): array
    {
        $limit = count($alreadySelectIds) + 10;

        $data = $this->model->select('id', 'name', 'sku')
            ->where(function ($query) use ($field, $searchTerm, $alreadySelectIds) {
                $query->when(!empty($searchTerm), function ($query) use ($field, $searchTerm) {
                    $searchTerm = addslashes($searchTerm);
                    $fields =  explode(",", $field);
                    foreach ($fields as $field) {
                        $query->orWhere(trim($field), 'LIKE', "%{$searchTerm}%");
                    }
                })->when(!empty($alreadySelectIds), function ($query) use ($alreadySelectIds) {
                    $query->orWhereIn('id', $alreadySelectIds);
                });
            })
            ->whereNotIn('id', $excludedIds)
            ->limit($limit)
            ->offset(0)
            ->get();

        $preparedData = [];

        foreach ($data as $item) {
            $preparedData[] = [
                'value' => $item->id,
                'label' => $item->name . ' (' . $item->sku . ')',
            ];
        }

        return $preparedData;
    }

    public function getItemInfoBaseQuery(array $data, array $billingCountryCodes, array $shippingCountryCodes, ?string $dateType)
    {
        return $this->model->join('order_item_parents', 'order_item_parents.item_id', '=', 'items.id')
            ->join('order_items', 'order_items.id', '=', 'order_item_parents.order_item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('order_billing_addresses', 'orders.id', '=', "order_billing_addresses.order_id")
            ->join('order_shipping_addresses', 'orders.id', '=', 'order_shipping_addresses.order_id')
            ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
            ->whereNull('orders.full_reshipment')
            ->where('items.id', $data['item_id'])
            ->when($dateType !== null, function ($query) use ($data, $dateType) {
                $query->where('orders.created_at', '>=', $data["{$dateType}order_date_from"])
                    ->where('orders.created_at', '<=', to_date_at_the_end($data["{$dateType}order_date_to"]));
            })
            ->when(!empty($billingCountryCodes), function ($query) use ($billingCountryCodes) {
                $query->whereIn('order_billing_addresses.country_code', $billingCountryCodes);
            })
            ->when(!empty($shippingCountryCodes), function ($query) use ($shippingCountryCodes) {
                $query->whereIn('order_shipping_addresses.country_code', $shippingCountryCodes);
            });
    }

    public function getInfoForItemAnalyticsByDaily(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType)
    {
        return $this->getItemInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, $dateType)
            ->selectRaw(
                'DATE(orders.created_at) AS order_date,
                SUM(order_item_parents.quantity) AS total_sold'
            )
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->get()
            ->keyBy('order_date');
    }

    public function getInfoForItemAnalyticsByWeekly(array $data, array $billingCountryCodes, array $shippingCountryCodes, array $dateRanges)
    {
        return $this->getItemInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, null)
            ->selectRaw(
                $this->buildCaseStatement($dateRanges) . ',
             SUM(order_item_parents.quantity) AS total_sold'
            )
            ->where('orders.created_at', '>=', $dateRanges[0]['from'])
            ->where('orders.created_at', '<=', to_date_at_the_end(end($dateRanges)['to']))
            ->groupBy('date_interval')
            ->orderBy('date_interval', 'ASC')
            ->get()
            ->keyBy('date_interval');
    }

    private function buildCaseStatement(array $dateRanges): string
    {
        $caseStatement = "CASE";

        foreach ($dateRanges as $index => $range) {
            $caseStatement .= " WHEN orders.created_at BETWEEN '{$range['from']}' AND '{$range['to']} 23:59:59' THEN '{$range['from']} to {$range['to']} 23:59:59'";
        }

        $caseStatement .= " ELSE 'Other' END AS date_interval";

        return $caseStatement;
    }

    public function getItemForPartialRefund(int $itemId)
    {
        return $this->model->select('id', 'name', 'production_price', 'regular_price', 'sku')
            ->where('items.id', $itemId)
            ->first();
    }

    public function fetchDashboardTopItemsList(string $fromDate, string $toDate, string $orderingField, int $languageId)
    {
        return $this->model->selectRaw(
            'items.name as item_name,
            items.sku as item_sku,
            COALESCE(order_stats.total_sold, 0) AS total_sold,
            COALESCE(order_stats.production_price_total, 0) AS production_price_total
            '
        )
            ->leftJoinSub(
                DB::table('order_item_parents')
                    ->selectRaw(
                        'order_item_parents.item_id,
                            SUM(order_item_parents.quantity) AS total_sold,
                            SUM(order_item_parents.production_price * order_item_parents.quantity) AS production_price_total
                        '
                    )
                    ->join('order_items', 'order_items.id', '=', 'order_item_parents.order_item_id')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->when($languageId > -1, function ($query) use ($languageId) {
                        $query->where('orders.language_id', $languageId);
                    })
                    ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->whereNull('orders.full_reshipment')
                    ->where('orders.created_at', '>=', $fromDate)
                    ->where('orders.created_at', '<=', to_date_at_the_end($toDate))
                    ->groupBy('order_item_parents.item_id'),
                'order_stats',
                'items.id',
                '=',
                'order_stats.item_id'
            )
            ->orderBy($orderingField, 'desc')
            ->limit(10)
            ->get();
    }

    public function fetchForDailyChecking(): array
    {
        $fullData = [];

        $this->model->select('id', 'stock_status', 'status')
            ->chunkById(100, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'items.id', 'id');


        return $fullData;
    }
}
