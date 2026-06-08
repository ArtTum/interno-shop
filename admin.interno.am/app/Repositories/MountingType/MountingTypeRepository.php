<?php

namespace App\Repositories\MountingType;

use App\Repositories\BaseRepository;
use App\Models\MountingType;

class MountingTypeRepository extends BaseRepository
{
   public function __construct(MountingType $model)
    {
        $this->model = $model;
    }
}