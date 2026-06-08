<?php

namespace App\Repositories\UserGroupPermission;

use App\Models\UserGroupPermission;
use App\Repositories\BaseRepository;
use App\Repositories\UserGroupPermission\Interfaces\UserGroupPermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserGroupPermissionRepository extends BaseRepository implements UserGroupPermissionRepositoryInterface
{
    public function __construct(UserGroupPermission $model)
    {
        $this->model = $model;
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->where('user_group_id', $params['user_group_id'])
            ->get()
            ->keyBy('name');
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
