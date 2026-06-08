<?php

namespace App\Repositories\Lamp;

use App\Repositories\BaseRepository;
use App\Models\Lamp;

class LampRepository extends BaseRepository
{
   public function __construct(Lamp $model)
    {
        $this->model = $model;
    }
}