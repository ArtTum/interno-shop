<?php

namespace App\Repositories\TntConsignmentNoteNumber;

use App\Repositories\BaseRepository;
use App\Models\TntConsignmentNoteNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TntConsignmentNoteNumberRepository extends BaseRepository
{
   public function __construct(TntConsignmentNoteNumber $model)
    {
        $this->model = $model;
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->get();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {

        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when((isset($params['status']) && $params['status'] >= 0), function ($statusQuery) use ($params) {
                $statusQuery->where('used', $params['status']);
            });
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function checkConsignmentNumber(int $orderId): bool
    {
        return $this->model
            ->where('order_number', $orderId)
            ->where('used', 0)
            ->exists();
    }

    public function getConsignmentNumber()
    {
        return $this->model
            ->where('used', 0)
            ->value('consignment_note_number');
    }


    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

}
