<?php

namespace App\Repositories\Product\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ProductRepositoryInterface
{
    public function create(array $data): Model;

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function delete(int $id): bool;

    public function fetchAsProductParam(int $languageId): Collection;

    public function fetchRelatedsByProductId(int $productId): Model;
}
