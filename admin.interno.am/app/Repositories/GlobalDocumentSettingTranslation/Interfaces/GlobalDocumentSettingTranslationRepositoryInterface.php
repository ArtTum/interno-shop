<?php

namespace App\Repositories\GlobalDocumentSettingTranslation\Interfaces;

interface GlobalDocumentSettingTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function delete(int $id): bool;
}
