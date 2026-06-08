<?php

namespace App\Repositories\MaterialSubstrate;

use App\Repositories\BaseRepository;
use App\Models\MaterialSubstrate;

class MaterialSubstrateRepository extends BaseRepository
{
   public function __construct(MaterialSubstrate $model)
    {
        $this->model = $model;
    }
}