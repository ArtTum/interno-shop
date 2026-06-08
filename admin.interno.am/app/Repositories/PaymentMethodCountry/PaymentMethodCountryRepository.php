<?php

namespace App\Repositories\PaymentMethodCountry;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethodCountry;

class PaymentMethodCountryRepository extends BaseRepository
{
    public function __construct(PaymentMethodCountry $model)
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

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchIdsByMethodId(int $methodId): array
    {
        return $this->model->select('country_id')->where('payment_method_id', $methodId)->pluck('country_id')->toArray();
    }

    public function deleteByMethodAndCountryIds(int $methodId, array $countryIds): bool
    {
        return $this->model->where('payment_method_id', $methodId)->whereIn('country_id', $countryIds)->delete();
    }

    public function getIdsByMethodId(int $id)
    {
        return $this->model->select('country_id')->where('payment_method_id', $id)->pluck('country_id')->toArray();
    }
}
