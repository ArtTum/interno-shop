<?php

namespace App\Repositories\ProductTranslation\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface ProductTranslationRepositoryInterface
{
    public function create(array $data): Model;

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function delete(int $id): bool;
}
