<?php

namespace App\Repositories\CheckWildberries;

use App\Repositories\BaseRepository;
use App\Models\CheckWildberries;

class CheckWildberriesRepository extends BaseRepository
{
   public function __construct(CheckWildberries $model)
    {
        $this->model = $model;
    }
}