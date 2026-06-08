<?php

namespace App\Repositories\Ip;

use App\Repositories\BaseRepository;
use App\Models\Ip;

class IpRepository extends BaseRepository
{
   public function __construct(Ip $model)
    {
        $this->model = $model;
    }
}