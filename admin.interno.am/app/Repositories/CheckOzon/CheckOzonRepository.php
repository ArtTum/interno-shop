<?php

namespace App\Repositories\CheckOzon;

use App\Repositories\BaseRepository;
use App\Models\CheckOzon;

class CheckOzonRepository extends BaseRepository
{
   public function __construct(CheckOzon $model)
    {
        $this->model = $model;
    }
}