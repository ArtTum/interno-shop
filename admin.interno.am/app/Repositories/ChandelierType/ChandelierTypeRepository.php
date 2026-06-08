<?php

namespace App\Repositories\ChandelierType;

use App\Repositories\BaseRepository;
use App\Models\ChandelierType;

class ChandelierTypeRepository extends BaseRepository
{
   public function __construct(ChandelierType $model)
    {
        $this->model = $model;
    }
}