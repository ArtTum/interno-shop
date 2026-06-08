<?php

namespace App\Repositories\SharedCart;

use App\Constants\OrderConstants;
use App\Repositories\BaseRepository;
use App\Models\SharedCart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SharedCartRepository extends BaseRepository
{
    public function __construct(SharedCart $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    private function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($params['created_at_from']), function ($query) use ($params) {
                $query->where('created_at', '>=', $params['created_at_from']);
            })
            ->when(!empty($params['created_at_to']), function ($query) use ($params) {
                $query->where('created_at', '<=', $params['created_at_to'] . ' 23:59:59');
            })
            ->when(empty($params['all']), function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
            ->when($params['user_group_id'] > 0, function ($query) use ($params) {
                $query->whereHas('user', function ($query) use ($params) {
                    $query->where('user_group_id', $params['user_group_id']);
                });
            })
            ->when($params['user_group_id'] == 0, function ($query) use ($params) {
                $query->whereNull('user_id');
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'name', 'last_name', 'user_group_id')
                        ->with([
                            'user_group' => function ($query) {
                                $query->select('id', 'name');
                            }
                        ]);
                }
            ])
            ->withCount([
                'order_infos' => function ($query) use ($params) {
                    $query->whereHas('order', function ($query) use ($params) {
                        $query->where('status', OrderConstants::STATUS_COMPLETED);
                    });
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function findAndIncreaseOpened(int $id): void
    {
        $sharedCart = $this->model->select('id', 'opened')
            ->where('id', $id)
            ->first();

        $sharedCart->update([
            'opened' => $sharedCart->opened + 1
        ]);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
