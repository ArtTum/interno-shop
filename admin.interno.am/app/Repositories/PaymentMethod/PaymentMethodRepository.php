<?php

namespace App\Repositories\PaymentMethod;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentMethodRepository extends BaseRepository
{
    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
    }

    public function fetchForBackend(bool $withTranslation, array $searchFields, $params)
    {
        return $this->model->select('payment_methods.id as id', 'type', 'payment_key', 'name', 'priority', 'status')
            ->leftJoin('payment_method_translations', function ($join) use ($params) {
                $join->on('payment_method_translations.payment_method_id', '=', 'payment_methods.id')
                    ->where('payment_method_translations.language_id', $params['base_language_id']);
            })
            ->when($params['country_id'] > -1, function ($query) use ($params) {
                $query->whereHas('countries_pivot', function ($query) use ($params) {
                    $query->where('country_id', $params['country_id']);
                });
            })
            ->when($params['currency_id'] > -1, function ($query) use ($params) {
                $query->whereHas('currencies_pivot', function ($query) use ($params) {
                    $query->where('currency_id', $params['currency_id']);
                });
            })
            ->when($withTranslation, function ($query) use ($params) {
                $query->whereHas('payment_method_translation', function ($query) use ($params) {
                    $query->where('language_id', $params['base_language_id']);
                });
            })
            ->when(!$withTranslation, function ($query) use ($params) {
                $query->whereDoesntHave('payment_method_translation', function ($query) use ($params) {
                    $query->where('language_id', $params['base_language_id']);
                });
            })
            ->when(!empty($searchFields) && ($params['search'] ?? ''), function ($searchQuery) use ($searchFields, $params) {
                $searchTerm = $params['search'];
                $searchQuery->where(function ($query) use ($searchTerm, $searchFields, $params) {

                    foreach ($searchFields as $fieldOrArray) {
                        $searchTerm = addslashes($searchTerm);
                        $query->orWhere($fieldOrArray, 'LIKE', "%$searchTerm%");
                    }
                });
            })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'payment_method_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'payment_method_id', 'description', 'name')->where('language_id', $languageId);
                    }
                ]);
            })
            ->with([
                'currencies_pivot' => function ($query) {
                    $query->select('payment_method_id', 'currency_id');
                },
                'countries_pivot' => function ($query) {
                    $query->select('payment_method_id', 'country_id');
                },
                'customer_groups_pivot' => function ($query) {
                    $query->select('payment_method_id', 'customer_group_id');
                },
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function fetchForCheckout(int $languageId, string $currency, int $billingCountryId, $b2b)
    {
        return $this->model->select('type', 'payment_key', 'name', 'description')
            ->join('payment_method_translations', 'payment_methods.id', '=', 'payment_method_translations.payment_method_id')
            ->where('payment_method_translations.language_id', $languageId)
            ->whereHas('currencies', function ($query) use ($currency) {
                $query->where('code', $currency);
            })
            ->whereHas('countries_pivot', function ($query) use ($billingCountryId) {
                $query->where('country_id', $billingCountryId);
            })
            ->when($b2b, function ($query) {
                $query->where(function ($q) {
                    $q->orWhereDoesntHave('customer_groups_pivot')
                        ->when(Auth::user()?->customer_group_id, function ($qw) {
                            $qw->orWhereHas('customer_groups_pivot', function ($q) {
                                $q->where('customer_group_id', Auth::user()->customer_group_id);
                            });
                        });
                });
            })
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->orderBy('payment_methods.id', 'asc')
            ->get();
    }

    public function getNameByKeyAndLanguage(int $languageId, string $paymentKey)
    {
        return $this->model->select('name')
            ->join('payment_method_translations', 'payment_methods.id', '=', 'payment_method_translations.payment_method_id')
            ->where('payment_key', $paymentKey)
            ->where('language_id', $languageId)
            ->first();
    }

    public function fetchForDashboard(int $languageId)
    {
        return $this->model->select('payment_key', 'status', 'name')
            ->join('payment_method_translations', 'payment_methods.id', 'payment_method_translations.payment_method_id')
            ->where('payment_method_translations.language_id', $languageId)
            ->get();
    }
}
