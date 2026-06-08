<?php

namespace App\Repositories\CategoryTranslation\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CategoryTranslationRepositoryInterface
{
    public function create(array $data): Model;

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;

    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function delete(int $id): bool;
}
