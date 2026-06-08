<?php

namespace App\Repositories\ShippingLabelSetting;

use App\Models\ShippingLabelSetting;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingLabelSetting\Interfaces\ShippingLabelSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShippingLabelSettingRepository extends BaseRepository implements ShippingLabelSettingRepositoryInterface
{
    public function __construct(ShippingLabelSetting $model)
    {
        $this->model = $model;
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields, $join = false): ?Model
    {
        return self::fetchQuery($selectedFields, [], [], [], [], [])
            ->where('shipping_label_settings.'.$whereField, $whereValue)
            ->when($join, function ($query) {
                $query->leftJoin('shipping_label_setting_collection_details',
                'shipping_label_setting_collection_details.shipping_label_setting_id','=',
                'shipping_label_settings.id');
            })
            ->with([
                'countries' => function ($countryQuery) {
                    $countryQuery->select('countries.id', 'code');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
