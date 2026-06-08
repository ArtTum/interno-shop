<?php

namespace App\Repositories\MediaSetting;

use App\Models\MediaSetting;
use App\Repositories\BaseRepository;
use App\Repositories\MediaSetting\Interfaces\MediaSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MediaSettingRepository extends BaseRepository implements MediaSettingRepositoryInterface
{
    public function __construct(MediaSetting $model)
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
