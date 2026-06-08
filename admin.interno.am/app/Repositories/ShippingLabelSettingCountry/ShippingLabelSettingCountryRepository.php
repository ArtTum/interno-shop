<?php

namespace App\Repositories\ShippingLabelSettingCountry;

use App\Models\ShippingLabelSettingCountry;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingLabelSettingCountry\Interfaces\ShippingLabelSettingCountryRepositoryInterface;

class ShippingLabelSettingCountryRepository extends BaseRepository implements ShippingLabelSettingCountryRepositoryInterface
{
    public function __construct(ShippingLabelSettingCountry $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByParams(string $whereField, string|int $whereValue): bool
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
