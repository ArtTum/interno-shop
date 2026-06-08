<?php

namespace App\Repositories\TopMenu;

use App\Repositories\BaseRepository;
use App\Models\TopMenu;

class TopMenuRepository extends BaseRepository
{
   public function __construct(TopMenu $model)
    {
        $this->model = $model;
    }
}