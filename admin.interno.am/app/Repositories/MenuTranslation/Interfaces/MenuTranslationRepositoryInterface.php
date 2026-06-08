<?php

namespace App\Repositories\MenuTranslation\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface MenuTranslationRepositoryInterface
{
    public function create(array $data): Model;
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function delete(int $id): bool;
}
