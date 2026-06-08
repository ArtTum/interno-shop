<?php

namespace App\Repositories\User\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function create(array $data): Model;
    public function loginAttempt(array $data): bool;
    public function fetch(): Model;

    public function insert(array $data): bool;
    public function fetchUser(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function fetch2(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins);
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;
    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields, array $with);
    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;
    public function delete(int $id): bool;
}
