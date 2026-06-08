<?php

namespace App\Repositories\Brand;

use App\Repositories\BaseRepository;
use App\Models\Brand;

class BrandRepository extends BaseRepository
{
   public function __construct(Brand $model)
    {
        $this->model = $model;
    }
}