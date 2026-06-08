<?php

namespace App\Repositories\PaymentMethodCurrency;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethodCurrency;

class PaymentMethodCurrencyRepository extends BaseRepository
{
    public function __construct(PaymentMethodCurrency $model)
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
        return $this->model->select('currency_id')->where('payment_method_id', $methodId)->pluck('currency_id')->toArray();
    }

    public function deleteByMethodAndCurrencyIds(int $methodId, array $currencyIds): bool
    {
        return $this->model->where('payment_method_id', $methodId)->whereIn('currency_id', $currencyIds)->delete();
    }

    public function getIdsByMethodId(int $id)
    {
        return $this->model->select('currency_id')->where('payment_method_id', $id)->pluck('currency_id')->toArray();
    }
}
