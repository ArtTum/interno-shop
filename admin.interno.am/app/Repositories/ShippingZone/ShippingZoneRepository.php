<?php

namespace App\Repositories\ShippingZone;

use App\Models\ShippingZone;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingZone\Interfaces\ShippingZoneRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingZoneRepository extends BaseRepository implements ShippingZoneRepositoryInterface
{
    public function __construct(ShippingZone $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'countries' => function ($countryQuery) {
                    $countryQuery->select('name', 'code');
                },
                'shipping_zone_methods' => function ($countryQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $countryQuery->select('shipping_zone_id', 'shipping_zone_method_translations.name')
                        ->join('shipping_zone_method_translations', 'shipping_zone_method_translations.shipping_zone_method_id', '=', 'shipping_zone_methods.id')
                        ->where('language_id', $baseLanguageId);;
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return self::fetchQuery($selectedFields, [], [], [], [], [])
            ->where($whereField, $whereValue)
            ->with([
                'countries' => function ($countryQuery) {
                    $countryQuery->select('countries.id', 'code');
                },
                'shipping_zone_methods' => function ($countryQuery) use ($languageId) {
                    $countryQuery->select('id', 'shipping_zone_id', 'carrier', DB::raw('false as deleted'), 'default')
                        ->with([
                            'shipping_zone_method_customer_groups' => function ($flatRateQuery) use ($languageId) {
                                $flatRateQuery->select('shipping_zone_method_id', 'customer_group_id');
                            },
                            'shipping_method_translation' => function ($flatRateQuery) use ($languageId) {
                                $flatRateQuery->select('id', 'shipping_zone_method_id', 'name', 'description')->where('language_id', $languageId);
                            },
                            'flat_rate' => function ($flatRateQuery) {
                                $flatRateQuery->select('id', 'shipping_zone_method_id', 'taxable', 'cost', 'hide_if_more', 'hide_if_less', 'some_day_delivery');
                            },
                            'free' => function ($flatRateQuery) {
                                $flatRateQuery->select('id', 'shipping_zone_method_id', 'requirement_type', 'min_order_amount', 'min_order_before_coupon');
                            }
                        ]);
                }])
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

    public function getByCountryId(int $countryId, int $languageId, float $rate, $b2b, $userLevelId = null): Collection
    {
        return $this->model->select('shipping_zone_methods.id as id', 'shipping_zone_methods.carrier as carrier',
            'shipping_zone_method_flat_rates.id as fl_id', 'shipping_zone_method_frees.id as fr_id',
            'shipping_zone_method_translations.name', 'shipping_zone_method_translations.description', 'shipping_zone_method_flat_rates.taxable',
            DB::raw("shipping_zone_method_flat_rates.cost * {$rate} as cost"),
            DB::raw("shipping_zone_method_flat_rates.hide_if_more * {$rate} as hide_if_more"),
            DB::raw("shipping_zone_method_flat_rates.hide_if_less * {$rate} as hide_if_less"),
            'shipping_zone_method_flat_rates.some_day_delivery',
            'shipping_zone_method_frees.requirement_type',
            DB::raw("shipping_zone_method_frees.min_order_amount * {$rate} as min_order_amount"),
            'shipping_zone_method_frees.min_order_before_coupon')
            ->join('shipping_zone_countries', 'shipping_zone_countries.shipping_zone_id', 'shipping_zones.id')
            ->where('shipping_zone_countries.country_id', $countryId)
            ->join('shipping_zone_methods', 'shipping_zone_methods.shipping_zone_id', 'shipping_zones.id')
            ->join('shipping_zone_method_translations', 'shipping_zone_method_translations.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->where('shipping_zone_method_translations.language_id', $languageId)
            ->leftJoin('shipping_zone_method_flat_rates', 'shipping_zone_method_flat_rates.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->leftJoin('shipping_zone_method_frees', 'shipping_zone_method_frees.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->leftJoin('shipping_zone_method_customer_groups', 'shipping_zone_method_customer_groups.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->when($b2b, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('shipping_zone_method_customer_groups.customer_group_id')
                        ->when(Auth::user()?->customer_group_id, function ($qw) {
                            $qw->orWhere('shipping_zone_method_customer_groups.customer_group_id', Auth::user()->customer_group_id);
                        });
                });
            })
            ->when($userLevelId, function ($query) use ($userLevelId) {
                $query->leftJoin('shipping_zone_method_user_levels as zvl', function ($join) use ($userLevelId) {
                    $join->on('zvl.shipping_zone_method_id', '=', 'shipping_zone_methods.id')
                        ->where('zvl.user_level_id', '=', $userLevelId);
                })
                    ->where(function ($q) {
                        $q->whereNull('zvl.excluded')->orWhere('zvl.excluded', 0);
                    });
            })
            ->orderBy('shipping_zone_methods.default', 'desc')
            ->orderBy('shipping_zone_methods.id', 'asc')
            ->get();
    }
}
