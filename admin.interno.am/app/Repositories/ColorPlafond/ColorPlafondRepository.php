<?php

namespace App\Repositories\ColorPlafond;

use App\Repositories\BaseRepository;
use App\Models\ColorPlafond;

class ColorPlafondRepository extends BaseRepository
{
   public function __construct(ColorPlafond $model)
    {
        $this->model = $model;
    }
}