<?php

namespace App\Repositories\GeneralSettingTranslation\Interfaces;

interface GeneralSettingTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
    public function delete(int $id): bool;
}
