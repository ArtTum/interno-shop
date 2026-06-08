<?php

namespace App\Repositories\TaxOrderFile\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TaxOrderFileRepositoryInterface
{
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
