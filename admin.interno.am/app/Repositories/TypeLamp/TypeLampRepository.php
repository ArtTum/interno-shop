<?php

namespace App\Repositories\TypeLamp;

use App\Repositories\BaseRepository;
use App\Models\TypeLamp;

class TypeLampRepository extends BaseRepository
{
   public function __construct(TypeLamp $model)
    {
        $this->model = $model;
    }
}