<?php

namespace App\Repositories\Feed;

use App\Constants\UserConstants;
use App\Models\Feed;
use App\Repositories\BaseRepository;
use App\Repositories\Feed\Interfaces\FeedRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FeedRepository extends BaseRepository implements FeedRepositoryInterface
{
    public function __construct(Feed $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function deleteByIds(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getFeedIdsByFeedTypeId($feedTypeId): array
    {
        return $this->model->where('feed_type_id', $feedTypeId)->orderBy('priority', 'asc')->pluck('id')->toArray();
    }
}
