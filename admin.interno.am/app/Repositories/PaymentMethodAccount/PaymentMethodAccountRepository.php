<?php

namespace App\Repositories\PaymentMethodAccount;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethodAccount;

class PaymentMethodAccountRepository extends BaseRepository
{
    public function __construct(PaymentMethodAccount $model)
    {
        $this->model = $model;
    }

    public function checkExists(int $paymentMethodId, int $currencyId, int $countryIds)
    {
        return $this->model->select('id')
            ->where('payment_method_id', $paymentMethodId)
            ->where('currency_id', $currencyId)
            ->where('country_id', $countryIds)
            ->exists();
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
        return parent::delete($id);
    }

    public function updateAllBasesToFalse(int $paymentMethodId): bool
    {
        return $this->model->where('payment_method_id', $paymentMethodId)
            ->update(['is_base' => false]);
    }

    public function fetchForMethod(int $paymentMethodId, ?array $currencyIds, ?array $countryIds, array $pagination)
    {
        return $this->fetchForMethodQuery($paymentMethodId, $currencyIds, $countryIds)
            ->select('id', 'info', 'is_base', 'country_id', 'currency_id', 'payment_method_id')
            ->when(!empty($pagination), function ($q) use ($pagination) {
                $q->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->orderBy('is_base', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function fetchForMethodQuery(int $paymentMethodId, ?array $currencyIds, ?array $countryIds)
    {
        return $this->model->where('payment_method_id', $paymentMethodId)
            ->when($currencyIds, function ($q) use ($currencyIds) {
                $q->whereIn('currency_id', $currencyIds);
            })
            ->when($countryIds, function ($q) use ($countryIds) {
                $q->whereIn('country_id', $countryIds);
            });
    }

    public function fetchForMethodTotalCount(int $paymentMethodId, ?array $currencyIds, ?array $countryIds)
    {
        return self::fetchForMethodQuery($paymentMethodId, $currencyIds, $countryIds)->count();
    }

    public function deleteAccounts(int $paymentMethodId, bool $deleteInvalids)
    {
        return $this->model->where('payment_method_id', $paymentMethodId)
            ->when($deleteInvalids, function ($q) use ($deleteInvalids) {
                $q->whereNull('info');
            })
            ->delete();
    }

    public function deleteByRelation(int $paymentInfoId, array $relationIds, string $relationStringName)
    {
        return $this->model
            ->where('payment_method_id', $paymentInfoId)
            ->whereIn($relationStringName, $relationIds)
            ->delete();
    }

    public function getAccountByCountryAndCurrency(string $paymentKey, int $currencyId, ?int $countryId)
    {
        return $this->model->select('payment_method_id', 'id', 'info')
            ->whereHas('payment_method', function ($q) use ($paymentKey, $currencyId, $countryId) {
                $q->where('payment_key', $paymentKey);
            })
            ->where('currency_id', $currencyId)
            ->when($countryId, function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            })
            ->whereNotNull('info')
            ->value('info');
    }

    public function getBaseByKey(string $paymentKey)
    {
        return $this->model->select('info')
            ->whereHas('payment_method', function ($q) use ($paymentKey) {
                $q->where('payment_key', $paymentKey);
            })
            ->where('is_base', true)
            ->whereNotNull('info')
            ->value('info');
    }
}
