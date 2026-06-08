<?php

namespace App\Repositories\Lead;

use App\Constants\OrderConstants;
use App\Models\OrderBillingAddress;
use App\Repositories\BaseRepository;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LeadRepository extends BaseRepository
{
    public function __construct(Lead $model)
    {
        $this->model = $model;
    }

    private function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['language_id'] > -1, function ($query) use ($params) {
                $query->where('language_id', $params['language_id']);
            })
            ->when($params['has_orders'] == 1, function ($query) use ($params) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('order_billing_addresses')
                        ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                        ->whereColumn('order_billing_addresses.email', 'leads.email')
                        ->whereColumn('orders.created_at', '>', 'leads.created_at')
                        ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED]);
                });
            })
            ->when(!empty($params['alerting_leads']), function ($query) use ($params) {
                $query->where('status_2_reminder', '<', now());
            })
            ->when($params['has_orders'] == 0, function ($query) use ($params) {
                $query->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('order_billing_addresses')
                        ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                        ->whereColumn('order_billing_addresses.email', 'leads.email')
                        ->whereColumn('orders.created_at', '>', 'leads.created_at')
                        ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED]);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->addSelect([
                'orders_count' => OrderBillingAddress::selectRaw('COUNT(*)')
                    ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                    ->whereColumn('order_billing_addresses.email', 'leads.email')
                    ->whereColumn('orders.created_at', '>', 'leads.created_at')
                    ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED]),
                'revenue' => OrderBillingAddress::selectRaw(
                    "ROUND(SUM(orders.total_price / NULLIF(orders.order_currency_rate, 0)), 2)"
                )
                    ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                    ->whereColumn('order_billing_addresses.email', 'leads.email')
                    ->whereColumn('orders.created_at', '>', 'leads.created_at')
                    ->whereIn('orders.status', [
                        OrderConstants::STATUS_PROCESSING,
                        OrderConstants::STATUS_COMPLETED
                    ]),

            ])
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['language_id'] > -1, function ($query) use ($params) {
                $query->where('language_id', $params['language_id']);
            })
            ->count();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function getLeadsForSendOffer()
    {
        // Get leads where "created_at" earlier 40 minutes than now
        return $this->model
            ->where('email_sent', 0)
            ->where('created_at', '<', now()->subMinutes(40))
            ->get()
            ->toArray();
    }

    public function fetchById(string $whereField, string|int $whereValue, string $selectedFields): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function getLeadsForExport(array $ordering, array $params, $searchFields): Collection
    {
        return self::fetchQuery('*', [], $ordering, $params, $searchFields, [])
            ->addSelect([
                'order_ids' => OrderBillingAddress::selectRaw("GROUP_CONCAT(CONCAT('#', orders.id) SEPARATOR '| ')")
                    ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                    ->whereColumn('order_billing_addresses.email', 'leads.email')
                    ->whereColumn('orders.created_at', '>', 'leads.created_at')
                    ->whereIn('orders.status', [
                        OrderConstants::STATUS_PROCESSING,
                        OrderConstants::STATUS_COMPLETED
                    ]),
                'revenues' => OrderBillingAddress::selectRaw(
                    "GROUP_CONCAT(CONCAT(FORMAT(orders.total_price, 2, 'de_DE'), orders.order_currency) SEPARATOR ' | ')"
                )
                    ->join('orders', 'order_billing_addresses.order_id', '=', 'orders.id')
                    ->whereColumn('order_billing_addresses.email', 'leads.email')
                    ->whereColumn('orders.created_at', '>', 'leads.created_at')
                    ->whereIn('orders.status', [
                        OrderConstants::STATUS_PROCESSING,
                        OrderConstants::STATUS_COMPLETED
                    ]),
            ])
            ->get();
    }
}
