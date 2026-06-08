<?php

namespace App\Repositories\UserBillingAddress;

use App\Models\UserBillingAddress;
use App\Repositories\BaseRepository;
use App\Repositories\UserBillingAddress\Interfaces\UserBillingAddressRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserBillingAddressRepository extends BaseRepository implements UserBillingAddressRepositoryInterface
{
    public function __construct(UserBillingAddress $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
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

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function insertOrUpdate(string $whereField, string|int $whereValue, array $data): bool
    {
        $model = $this->model->select(DB::raw('id'))->where($whereField, $whereValue)->first();

        if (!empty($model)) {
            return parent::update($whereField, $whereValue, $data);
        } else {
            return parent::insert($data);
        }
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function getInfoForSendingToStatisticTracker(int $userId)
    {
        return $this->model->select('user_billing_addresses.name', 'last_name', 'city', 'zip', 'state', 'phone', 'email', 'countries.code as country_code')
            ->where('user_id', $userId)
            ->join('countries', 'countries.id', '=', 'user_billing_addresses.country_id')
            ->first();
    }
}
