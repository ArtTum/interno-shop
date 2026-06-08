<?php

namespace App\Repositories\Akcia;

use App\Repositories\BaseRepository;
use App\Models\Akcia;

class AkciaRepository extends BaseRepository
{
   public function __construct(Akcia $model)
    {
        $this->model = $model;
    }
}