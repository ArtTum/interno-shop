<?php

namespace App\Repositories\Supplie;

use App\Repositories\BaseRepository;
use App\Models\Supplie;

class SupplieRepository extends BaseRepository
{
   public function __construct(Supplie $model)
    {
        $this->model = $model;
    }
}