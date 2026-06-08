<?php

namespace App\Repositories\Social\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface SocialRepositoryInterface
{
    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection;
    public function update(string $whereField, string|int $whereValue, array $data): bool;
}
