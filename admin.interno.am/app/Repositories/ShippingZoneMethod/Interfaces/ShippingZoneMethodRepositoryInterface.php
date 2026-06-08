<?php

namespace App\Repositories\ShippingZoneMethod\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ShippingZoneMethodRepositoryInterface
{
    public function create(array $data): Model;
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;
    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;
    public function bulkDeleteByParams(string $whereField, array $whereValue): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
