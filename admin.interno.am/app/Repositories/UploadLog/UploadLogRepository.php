<?php

namespace App\Repositories\UploadLog;

use App\Models\UploadLog;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UploadLogRepository extends BaseRepository
{
    public function __construct(UploadLog $model)
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

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchForUpload(int $uploadId)
    {
        return  $this->model->select('id', 'upload_id', 'log')
            ->where('upload_id', $uploadId)
            ->get();
    }
}
