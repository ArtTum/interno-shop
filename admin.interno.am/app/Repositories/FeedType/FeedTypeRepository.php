<?php

namespace App\Repositories\FeedType;

use App\Constants\UserConstants;
use App\Models\FeedType;
use App\Repositories\BaseRepository;
use App\Repositories\FeedType\Interfaces\FeedTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeedTypeRepository extends BaseRepository implements FeedTypeRepositoryInterface
{
    public function __construct(FeedType $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
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

    public function all(string $select): Collection
    {
        return self::fetchQuery($select, [], [], [], [], [])->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with(['feeds' => function ($query) {
                $query->select(DB::raw('id, column_name, field_type, sku_prefix, custom_key, in_stock_value,
                 custom_value, out_of_stock_value, chars_limit, feed_type_id, priority, include_sub_name, square_image_ratio'))
                    ->orderBy('priority', 'asc');
            }])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}
