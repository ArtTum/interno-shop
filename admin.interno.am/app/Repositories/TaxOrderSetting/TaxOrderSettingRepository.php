<?php

namespace App\Repositories\TaxOrderSetting;

use App\Models\TaxOrderSetting;
use App\Repositories\BaseRepository;
use App\Repositories\TaxOrderSetting\Interfaces\TaxOrderSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TaxOrderSettingRepository extends BaseRepository implements TaxOrderSettingRepositoryInterface
{
    public function __construct(TaxOrderSetting $model)
    {
        $this->model = $model;
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->get();
    }
    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
