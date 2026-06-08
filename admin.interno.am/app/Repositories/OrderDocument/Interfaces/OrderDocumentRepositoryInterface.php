<?php

namespace App\Repositories\OrderDocument\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OrderDocumentRepositoryInterface
{
//    public function insert(array $data): bool;

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model;
}
