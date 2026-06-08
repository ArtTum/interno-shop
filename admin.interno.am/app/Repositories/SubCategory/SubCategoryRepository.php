<?php

namespace App\Repositories\SubCategory;

use App\Repositories\BaseRepository;
use App\Models\SubCategory;

class SubCategoryRepository extends BaseRepository
{
   public function __construct(SubCategory $model)
    {
        $this->model = $model;
    }
}