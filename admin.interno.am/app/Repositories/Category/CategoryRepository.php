<?php

namespace App\Repositories\Category;

use App\Repositories\BaseRepository;
use App\Models\Category;

class CategoryRepository extends BaseRepository
{
   public function __construct(Category $model)
    {
        $this->model = $model;
    }
}