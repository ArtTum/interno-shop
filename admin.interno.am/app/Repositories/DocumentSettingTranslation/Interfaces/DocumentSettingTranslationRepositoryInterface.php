<?php

namespace App\Repositories\DocumentSettingTranslation\Interfaces;

interface DocumentSettingTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function delete(int $id): bool;
}
