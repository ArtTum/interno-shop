<?php

namespace App\Repositories\ProductVariantAttribute\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface ProductVariantAttributeRepositoryInterface
{
    public function insert(array $data): bool;

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function delete(int $id): bool;
}
