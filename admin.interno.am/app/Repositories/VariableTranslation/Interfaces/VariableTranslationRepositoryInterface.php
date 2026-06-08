<?php

namespace App\Repositories\VariableTranslation\Interfaces;

interface VariableTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
