<?php

namespace App\Repositories\ShippingZoneMethod;

use App\Models\ShippingZoneMethod;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingZoneMethod\Interfaces\ShippingZoneMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShippingZoneMethodRepository extends BaseRepository implements ShippingZoneMethodRepositoryInterface
{
    public function __construct(ShippingZoneMethod $model)
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
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }


    public function bulkDeleteByParams(string $whereField, array $whereValue): bool
    {
        return $this->model->whereIn($whereField, $whereValue)->delete();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function getForOrderCreate(int $shippingZoneId, int $languageId, float $rate)
    {
        return $this->model->select('shipping_zone_methods.id as id', 'carrier',
            'shipping_zone_method_translations.name', 'shipping_zone_method_flat_rates.taxable',
            DB::raw("shipping_zone_method_flat_rates.cost * {$rate} as cost"), 'shipping_zone_method_flat_rates.some_day_delivery',
            'shipping_zone_method_flat_rates.some_day_delivery')
            ->where('shipping_zone_methods.id', $shippingZoneId)
            ->join('shipping_zone_method_translations', 'shipping_zone_method_translations.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->where('shipping_zone_method_translations.language_id', $languageId)
            ->leftJoin('shipping_zone_method_flat_rates', function ($join) use ($languageId) {
                $join->on('shipping_zone_method_flat_rates.shipping_zone_method_id', '=', 'shipping_zone_methods.id');
            })
            ->first();
    }

    public function getForMarketplaceOrderCreate(array $shippingZoneIds, int $languageId)
    {
        return $this->model->select('shipping_zone_methods.id as id', 'carrier', 'shipping_zone_method_translations.name', 'shipping_zone_method_flat_rates.some_day_delivery')
            ->whereIn('shipping_zone_methods.shipping_zone_id', $shippingZoneIds)
            ->where('shipping_zone_methods.default', true)
            ->join('shipping_zone_method_translations', 'shipping_zone_method_translations.shipping_zone_method_id', 'shipping_zone_methods.id')
            ->where('shipping_zone_method_translations.language_id', $languageId)
            ->leftJoin('shipping_zone_method_flat_rates', function ($join) use ($languageId) {
                $join->on('shipping_zone_method_flat_rates.shipping_zone_method_id', '=', 'shipping_zone_methods.id');
            })
            ->first();
    }
}
