<?php

namespace App\Repositories\MaterialBase;

use App\Repositories\BaseRepository;
use App\Models\MaterialBase;

class MaterialBaseRepository extends BaseRepository
{
   public function __construct(MaterialBase $model)
    {
        $this->model = $model;
    }
}