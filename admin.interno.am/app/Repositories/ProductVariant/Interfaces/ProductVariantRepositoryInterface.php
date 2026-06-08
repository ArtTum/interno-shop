<?php

namespace App\Repositories\ProductVariant\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface ProductVariantRepositoryInterface
{
    public function create(array $data): Model;

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function delete(int $id): bool;

    public function checkVariationExists(string $key): bool;
}
