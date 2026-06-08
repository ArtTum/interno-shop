<?php

namespace App\Repositories\MaterialSuspension;

use App\Repositories\BaseRepository;
use App\Models\MaterialSuspension;

class MaterialSuspensionRepository extends BaseRepository
{
   public function __construct(MaterialSuspension $model)
    {
        $this->model = $model;
    }
}