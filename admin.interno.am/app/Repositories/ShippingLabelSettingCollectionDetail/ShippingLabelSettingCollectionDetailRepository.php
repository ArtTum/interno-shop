<?php

namespace App\Repositories\ShippingLabelSettingCollectionDetail;

use App\Repositories\BaseRepository;
use App\Models\ShippingLabelSettingCollectionDetail;
use Illuminate\Database\Eloquent\Model;

class ShippingLabelSettingCollectionDetailRepository extends BaseRepository
{
    public function __construct(ShippingLabelSettingCollectionDetail $model)
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

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }
}
