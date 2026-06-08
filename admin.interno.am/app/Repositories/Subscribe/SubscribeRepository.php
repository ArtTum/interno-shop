<?php

namespace App\Repositories\Subscribe;

use App\Repositories\BaseRepository;
use App\Models\Subscribe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubscribeRepository extends BaseRepository
{
   public function __construct(Subscribe $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Builder {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when((!empty($params['year']) && $params['year'] > -1), function ($q) use ($params) {
                $q->where('year', $params['year']);
            })->when((!empty($params['month']) && $params['month'] > -1), function ($q) use ($params) {
                $q->where('month', $params['month']);
            });
    }

    public function fetch(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Collection {
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function fetchTotalCount(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): int {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByFieldWith(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
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

    public function getIdByGroup(string $key)
    {
        return $this->model->select('id')->value('id');
    }

    public function fetchCountByStatus(int $status): int
    {
        return $this->model->where('status', $status)->count();
    }
}
