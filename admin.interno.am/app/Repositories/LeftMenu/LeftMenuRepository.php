<?php

namespace App\Repositories\LeftMenu;

use App\Repositories\BaseRepository;
use App\Models\LeftMenu;

class LeftMenuRepository extends BaseRepository
{
   public function __construct(LeftMenu $model)
    {
        $this->model = $model;
    }
}