<?php

namespace App\Repositories\GlobalDocumentSetting\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface GlobalDocumentSettingRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder;

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, array $data): Model;

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int;

}
