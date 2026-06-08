<?php

namespace App\Repositories\Yml;

use App\Repositories\BaseRepository;
use App\Models\Yml;

class YmlRepository extends BaseRepository
{
   public function __construct(Yml $model)
    {
        $this->model = $model;
    }
}