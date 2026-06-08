<?php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use App\Repositories\BaseRepository;
use App\Repositories\Coupon\Interfaces\CouponRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['kind'] == 1, function ($query) use ($params) {
                $query->whereNull('gift_card_details');
            })
            ->when($params['only_my'], function ($query) use ($params) {
                $query->where('user_id', Auth::user()->id);
            })
            ->when($params['kind'] == 0, function ($query) use ($params) {
                $query->whereNotNull('gift_card_details');
            })
            ->when($params['status'] > -1, function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when($params['type'] > -1, function ($query) use ($params) {
                $query->where('type', $params['type']);
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->withCount('orders')
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'products' => function ($query) {
                    $query->select('coupon_id', 'product_id');
                },
                'excluded_products' => function ($query) {
                    $query->select('coupon_id', 'product_id');
                },
                'categories' => function ($query) {
                    $query->select('coupon_id', 'category_id');
                },
                'excluded_categories' => function ($query) {
                    $query->select('coupon_id', 'category_id');
                },
                'coupon_allowed_emails' => function ($query) {
                    $query->select('coupon_id', 'email');
                },
            ])
            ->withCount('orders')
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model
            ->with([
                'products' => function ($query) {
                    $query->select('coupon_id', 'product_id');
                },
                'excluded_products' => function ($query) {
                    $query->select('coupon_id', 'product_id');
                },
                'categories' => function ($query) {
                    $query->select('coupon_id', 'category_id');
                },
                'excluded_categories' => function ($query) {
                    $query->select('coupon_id', 'category_id');
                },
                'coupon_allowed_emails' => function ($query) {
                    $query->select('coupon_id', 'email');
                },
            ])->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('id', $data['ids'])
                    ->orderBy($data['ordering_field'], $data['ordering_direction']);;
            })->get();
    }

    public function getForCheckout(string $code, float $itemsSubtotal, float $rate)
    {
        return $this->model->select(
            'id', 'type', 'amount', 'usage_limit_per_user', 'usage_limit', 'gift_card_details'
        )->with([
            'coupon_allowed_emails' => function ($query) {
                $query->select('coupon_id', 'email');
            },
        ])
            ->where(function ($query) {
                $query->orWhere('expires_at', '>=', now()->toDateString())
                    ->orWhereNull('expires_at');
            })
            ->where(function ($query) use ($itemsSubtotal, $rate) {
                $query->where(DB::raw("min_spend * {$rate}"), '<=', $itemsSubtotal)
                    ->orWhereNull('min_spend');
            })
            ->where(function ($query) use ($itemsSubtotal, $rate) {
                $query->where(DB::raw("max_spend * {$rate}"), '>=', $itemsSubtotal)
                    ->orWhereNull('max_spend');
            })
            ->where('status', true)
            ->where('code', $code)
            ->first();
    }

    public function getIdByCode(string $code): ?int
    {
        return $this->model->select('id')->where('code', $code)->value('id');
    }

    public function fetchForAnalyticsFilter()
    {
        return $this->model->select('code as label', 'code as value')->get();
    }
}
