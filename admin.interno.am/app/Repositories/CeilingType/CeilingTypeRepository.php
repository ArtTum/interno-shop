<?php

namespace App\Repositories\CeilingType;

use App\Repositories\BaseRepository;
use App\Models\CeilingType;

class CeilingTypeRepository extends BaseRepository
{
   public function __construct(CeilingType $model)
    {
        $this->model = $model;
    }
}