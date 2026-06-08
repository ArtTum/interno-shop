<?php

namespace App\Repositories\Order\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface OrderRepositoryInterface
{
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;

    public function show(int $orderId, int $languageId): Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
