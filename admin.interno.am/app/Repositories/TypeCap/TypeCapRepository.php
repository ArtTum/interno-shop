<?php

namespace App\Repositories\TypeCap;

use App\Repositories\BaseRepository;
use App\Models\TypeCap;

class TypeCapRepository extends BaseRepository
{
   public function __construct(TypeCap $model)
    {
        $this->model = $model;
    }
}