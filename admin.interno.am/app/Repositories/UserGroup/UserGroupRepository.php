<?php

namespace App\Repositories\UserGroup;

use App\Constants\UserConstants;
use App\Models\UserGroup;
use App\Repositories\BaseRepository;
use App\Repositories\UserGroup\Interfaces\UserGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserGroupRepository extends BaseRepository implements UserGroupRepositoryInterface
{
    public function __construct(UserGroup $model)
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
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
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

    public function getIdByName(string $name)
    {
        return $this->model->select('id')
            ->where('name', $name)
            ->value('id');
    }

    public function getIdByGroup(string $key)
    {
        return $this->model->select('id')
            ->where('name', UserConstants::USER_GROUPS[$key])
            ->value('id');
    }

    public function getIdByGroupCreateIfNot(string $key)
    {
        $id = $this->model->select('id')
            ->where('name', UserConstants::USER_GROUPS[$key])
            ->value('id');

        if (!$id) {
            $userGroup = parent::create([
                'name' => UserConstants::USER_GROUPS[$key]
            ]);

            $id = $userGroup->id;
        }

        return $id;
    }
}
