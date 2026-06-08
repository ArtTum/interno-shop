<?php

namespace App\Repositories\OrderItem;

use App\Constants\OrderConstants;
use App\Models\OrderItem;
use App\Repositories\BaseRepository;
use App\Repositories\OrderItem\Interfaces\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    public function __construct(OrderItem $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function bulkDeleteByIds(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function clearTaxes(int $orderId)
    {
        return $this->model->where('order_id', $orderId)
            ->update([
                'tax_price' => 0,
                'total' => DB::raw('subtotal')
            ]);
    }

    public function fetchForPartialReshipment(int $orderId)
    {
        return $this->model->select('id', 'parent_id', 'quantity', 'product_variant_id')
            ->whereNull('parent_id')
            ->where('order_id', $orderId)
            ->with([
                'order_item_parents' => function ($query) {
                    $query->select('order_item_id', 'order_item_parents.name', 'order_item_parents.sku', 'order_item_parents.item_id',
                        'order_item_parents.quantity', 'items.net_weight', 'items.gross_weight', 'items.un_numbers',
                        'order_item_parents.production_price', 'order_item_parents.regular_price')
                        ->leftJoin('items', 'items.id', '=', 'order_item_parents.item_id');
                },
                'extra_products' => function ($query) {
                    $query->select('id', 'parent_id', 'product_variant_id')
                        ->with([
                            'order_item_parents' => function ($query) {
                                $query->select('order_item_id', 'order_item_parents.name', 'order_item_parents.sku', 'order_item_parents.item_id',
                                    'order_item_parents.quantity', 'items.net_weight', 'items.gross_weight', 'items.un_numbers',
                                    'order_item_parents.production_price', 'order_item_parents.regular_price')
                                    ->leftJoin('items', 'items.id', '=', 'order_item_parents.item_id');
                            }
                        ]);
                }
            ])
            ->get();
    }

    public function getProductInfoBaseQuery(array $data, array $billingCountryCodes, array $shippingCountryCodes, ?string $dateType)
    {
        return $this->model->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('order_billing_addresses', 'orders.id', '=', "order_billing_addresses.order_id")
            ->join('order_shipping_addresses', 'orders.id', '=', 'order_shipping_addresses.order_id')
            ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
            ->whereNull('orders.full_reshipment')
            ->where('order_items.product_id', $data['product_id'])
            ->when(!empty($data['product_attribute_ids']), function ($query) use ($data) {
                $query->whereHas('product_variant', function ($query) use ($data) {
                    foreach ($data['product_attribute_ids'] as $product_attribute_id) {
                        $query->whereHas('product_variant_attributes', function ($query) use ($product_attribute_id) {
                            $query->where('attribute_id', $product_attribute_id);
                        });
                    }
                });
            })
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

    public function getInfoForProductAnalyticsByDaily(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType)
    {
        return $this->getProductInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, $dateType)
            ->selectRaw(
                'DATE(orders.created_at) AS order_date,
                order_items.id,
                SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
            SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
            SUM(order_items.quantity) AS total_sold'
            )
            ->when(!empty($data['item_id']) && $data['item_id'] > -1, function ($q) use ($data) {
                $q->whereHas('order_item_parents', function ($q) use ($data) {
                    $q->where('item_id', $data['item_id']);
                })
                    ->with([
                        'order_item_parents' => function ($q) use ($data) {
                            $q->select('order_item_id', DB::raw('SUM(quantity) AS quantity'))
                                ->where('item_id', $data['item_id'])
                                ->groupBy('order_item_id');
                        },
                    ]);
            })
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->get()
            ->keyBy('order_date');
    }

    public function getInfoForProductAnalyticsByWeekly(array $data, array $billingCountryCodes, array $shippingCountryCodes, array $dateRanges)
    {
        return $this->getProductInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, null)
            ->selectRaw(
                $this->buildCaseStatement($dateRanges) . ',
            SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
            SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
            SUM(order_items.quantity) AS total_sold'
            )
            ->where('orders.created_at', '>=', $dateRanges[0]['from'])
            ->where('orders.created_at', '<=', to_date_at_the_end(end($dateRanges)['to']))
            ->groupBy('date_interval')
            ->orderBy('date_interval', 'ASC')
            ->get()
            ->keyBy('date_interval'); // Converts collection keys to interval labels
    }

    public function getCategoryInfoBaseQuery(array $data, array $billingCountryCodes, array $shippingCountryCodes, ?string $dateType, $categoryIds)
    {
        return $this->model->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('order_billing_addresses', 'orders.id', '=', "order_billing_addresses.order_id")
            ->join('order_shipping_addresses', 'orders.id', '=', 'order_shipping_addresses.order_id')
            ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
            ->whereNull('orders.full_reshipment')
            ->whereHas('product', function ($query) use ($categoryIds) {
                $query->whereHas('categories_pivot', function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                });
            })
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

    public function getInfoForCategoryAnalyticsByGeneral(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType, $categoryIds)
    {
        $parentSumsQuery = DB::table('order_item_parents')
            ->select('order_item_id', DB::raw('SUM(quantity) as parent_quantity'))
            ->when(!empty($data['item_id']) && $data['item_id'] > -1, function ($q) use ($data) {
                $q->where('item_id', $data['item_id']);
            })
            ->groupBy('order_item_id');

        return $this->getCategoryInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, $dateType, $categoryIds)
            ->selectRaw(
                'SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
                SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
                SUM(order_items.quantity) AS total_sold,
                COALESCE(SUM(parent_sums.parent_quantity), 0) AS parent_quantity'
            )
            ->leftJoinSub($parentSumsQuery, 'parent_sums', function ($join) {
                $join->on('parent_sums.order_item_id', '=', 'order_items.id');
            })
            ->orderBy('total_sold', 'desc')
            ->first()
            ->toArray();
    }

    public function getInfoForCategoryAnalyticsByDaily(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType, $categoryIds)
    {
        return $this->getCategoryInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, $dateType, $categoryIds)
            ->selectRaw(
                'DATE(orders.created_at) AS order_date,
                SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
            SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
            SUM(order_items.quantity) AS total_sold'
            )
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->get()
            ->keyBy('order_date');
    }

    public function getInfoForCategoryAnalyticsByWeekly(array $data, array $billingCountryCodes, array $shippingCountryCodes, array $dateRanges, $categoryIds)
    {
        return $this->getCategoryInfoBaseQuery($data, $billingCountryCodes, $shippingCountryCodes, null, $categoryIds)
            ->selectRaw(
                $this->buildCaseStatement($dateRanges) . ',
            SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
            SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
            SUM(order_items.quantity) AS total_sold'
            )
            ->where('orders.created_at', '>=', $dateRanges[0]['from'])
            ->where('orders.created_at', '<=', to_date_at_the_end(end($dateRanges)['to']))
            ->groupBy('date_interval')
            ->orderBy('date_interval', 'ASC')
            ->get()
            ->keyBy('date_interval'); // Converts collection keys to interval labels
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

    public function getTotalSalesForCLV(array $data, $categoryIds)
    {
        return $this->model->selectRaw(
            'SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal'
        )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$data['order_date_from'], to_date_at_the_end($data['order_date_to'])])
            ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
            ->whereNull('orders.full_reshipment')
            ->whereHas('product', function ($query) use ($categoryIds) {
                $query->whereHas('categories_pivot', function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                });
            })
            ->first();
    }


    public function fetchDashboardTopProductsList(string $fromDate, string $toDate, int $languageId, string $orderingField, int $filteringLanguageId)
    {
        return $this->model->selectRaw('
                order_items.product_id,
                SUM(order_items.total / orders.order_currency_rate) as total_sales,
                SUM(order_items.quantity) as total_sold
            ')
            ->with([
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'product_translations.name')
                        ->join('product_translations', 'product_translations.product_id', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($query) {
                                $query->select('product_id', 'sku', 'media_id')
                                    ->with([
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select([
                                                'id', 'original_path AS path'
                                            ]);
                                        }
                                    ]);
                            }
                        ]);
                }
            ])
            ->whereNotNull('product_id')
            ->join('orders', 'order_items.order_id', '=', "orders.id")
            ->whereNull('orders.full_reshipment')
            ->when($filteringLanguageId > -1, function ($query) use ($filteringLanguageId) {
                $query->where('orders.language_id', $filteringLanguageId);
            })
            ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
            ->where('orders.created_at', '>=', $fromDate)
            ->where('orders.created_at', '<=', to_date_at_the_end($toDate))
            ->groupBy('order_items.product_id')
            ->orderBy($orderingField, 'desc')
            ->limit(10)
            ->get();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }

    public function fetchInfoForCustomerInterestsProducts(int $userId, int $languageId)
    {
        return $this->model->select('product_translations.name', 'order_items.product_id as link_id')
            ->join('product_translations', 'product_translations.product_id', 'order_items.product_id')
            ->where('product_translations.language_id', $languageId)
            ->whereHas('order', function ($q) use ($userId) {
                $q->whereIn('status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->where('user_id', $userId);
            })
            ->groupBy('link_id')
            ->get();
    }

    public function fetchInfoForCustomerInterestsCategories(int $userId, int $languageId)
    {
        return $this->model->select('category_translations.name', 'product_categories.category_id as link_id')
            ->join('product_categories', 'order_items.product_id', '=', 'product_categories.product_id')
            ->join('category_translations', 'category_translations.category_id', 'product_categories.category_id')
            ->where('category_translations.language_id', $languageId)
            ->whereHas('order', function ($q) use ($userId) {
                $q->whereIn('status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->where('user_id', $userId);
            })
            ->groupBy('link_id')
            ->get();
    }
}
