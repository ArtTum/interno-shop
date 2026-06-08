<?php

namespace App\Repositories\Tax\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TaxRepositoryInterface
{
    public function insert(array $data): bool;
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;
    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;
}
