<?php

namespace App\Repositories\TireLength;

use App\Repositories\BaseRepository;
use App\Models\TireLength;

class TireLengthRepository extends BaseRepository
{
   public function __construct(TireLength $model)
    {
        $this->model = $model;
    }
}