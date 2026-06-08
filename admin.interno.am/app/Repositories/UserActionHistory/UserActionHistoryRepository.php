<?php

namespace App\Repositories\UserActionHistory;

use App\Repositories\BaseRepository;
use App\Models\UserActionHistory;

class UserActionHistoryRepository extends BaseRepository
{
   public function __construct(UserActionHistory $model)
    {
        $this->model = $model;
    }
}