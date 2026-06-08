<?php

namespace App\Repositories\TrustpilotSetting;

use App\Models\DgdSetting;
use App\Models\TrustpilotSetting;
use App\Repositories\BaseRepository;
use App\Repositories\DgdSetting\Interfaces\DgdSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrustpilotSettingRepository extends BaseRepository
{
    public function __construct(TrustpilotSetting $model)
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

    public function fetchByFirst(string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
    public function create(array $data): Model
    {
        return parent::create($data);
    }
}

