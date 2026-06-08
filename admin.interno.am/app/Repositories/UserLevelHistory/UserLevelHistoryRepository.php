<?php

namespace App\Repositories\UserLevelHistory;

use App\Repositories\BaseRepository;
use App\Models\UserLevelHistory;

class UserLevelHistoryRepository extends BaseRepository
{
   public function __construct(UserLevelHistory $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }


    public function getUserLevelHistoryForAccount(int $userId, array $pagination)
    {
        return self::getInfoForUserLevelHistoryTabQuery($userId)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->orderBy('id', 'desc')
            ->with(['order' => function ($query) {
                $query->select('id', 'order_currency');
            }])
            ->get();
    }

    public function getInfoForUserLevelHistoryTabCount(int $userId)
    {
        return self::getInfoForUserLevelHistoryTabQuery($userId)->count();
    }

    public function getInfoForUserLevelHistoryTabQuery(int $userId)
    {
        return $this->model
            ->select('id', 'user_id', 'order_id', 'initial_amount', 'final_amount', 'status', 'created_at');
    }
}
