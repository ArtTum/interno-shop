<?php

namespace App\Repositories\BottomMenu;

use App\Repositories\BaseRepository;
use App\Models\BottomMenu;

class BottomMenuRepository extends BaseRepository
{
   public function __construct(BottomMenu $model)
    {
        $this->model = $model;
    }
}