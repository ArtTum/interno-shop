<?php

namespace App\Repositories\Status;

use App\Repositories\BaseRepository;
use App\Models\Status;

class StatusRepository extends BaseRepository
{
   public function __construct(Status $model)
    {
        $this->model = $model;
    }
}