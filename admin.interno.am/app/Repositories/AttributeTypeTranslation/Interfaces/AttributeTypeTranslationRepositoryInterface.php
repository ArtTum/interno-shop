<?php

namespace App\Repositories\AttributeTypeTranslation\Interfaces;

interface AttributeTypeTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function delete(int $id): bool;
}
