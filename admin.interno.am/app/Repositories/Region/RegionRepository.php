<?php

namespace App\Repositories\Region;

use App\Repositories\BaseRepository;
use App\Models\Region;

class RegionRepository extends BaseRepository
{
   public function __construct(Region $model)
    {
        $this->model = $model;
    }
}