<?php

namespace App\Repositories\Country\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CountryRepositoryInterface
{
    public function insert(array $data, ?string $connection = null): bool;
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;
    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;
}
