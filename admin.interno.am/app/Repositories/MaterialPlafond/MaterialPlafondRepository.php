<?php

namespace App\Repositories\MaterialPlafond;

use App\Repositories\BaseRepository;
use App\Models\MaterialPlafond;

class MaterialPlafondRepository extends BaseRepository
{
   public function __construct(MaterialPlafond $model)
    {
        $this->model = $model;
    }
}