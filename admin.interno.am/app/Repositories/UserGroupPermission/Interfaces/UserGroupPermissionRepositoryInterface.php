<?php

namespace App\Repositories\UserGroupPermission\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface UserGroupPermissionRepositoryInterface
{
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
