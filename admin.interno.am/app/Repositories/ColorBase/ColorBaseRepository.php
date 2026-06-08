<?php

namespace App\Repositories\ColorBase;

use App\Repositories\BaseRepository;
use App\Models\ColorBase;

class ColorBaseRepository extends BaseRepository
{
   public function __construct(ColorBase $model)
    {
        $this->model = $model;
    }
}