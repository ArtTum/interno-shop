<?php

namespace App\Repositories\Media\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface MediaRepositoryInterface
{
    public function insert(array $data): bool;
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;
    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;
    public function all(string $selectedFields): Collection;
    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;
    public function fetchExists(string $whereField, string|int $whereValue): bool;
    public function delete(int $id): bool;
}
