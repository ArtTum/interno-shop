<?php

namespace App\Repositories\SuspensionType;

use App\Repositories\BaseRepository;
use App\Models\SuspensionType;

class SuspensionTypeRepository extends BaseRepository
{
   public function __construct(SuspensionType $model)
    {
        $this->model = $model;
    }
}