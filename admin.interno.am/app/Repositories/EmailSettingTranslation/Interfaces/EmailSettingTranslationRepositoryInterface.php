<?php

namespace App\Repositories\EmailSettingTranslation\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface EmailSettingTranslationRepositoryInterface
{
    public function insert(array $data): bool;
    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
