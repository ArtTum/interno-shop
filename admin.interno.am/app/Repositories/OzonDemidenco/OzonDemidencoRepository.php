<?php

namespace App\Repositories\OzonDemidenco;

use App\Repositories\BaseRepository;
use App\Models\OzonDemidenco;

class OzonDemidencoRepository extends BaseRepository
{
   public function __construct(OzonDemidenco $model)
    {
        $this->model = $model;
    }
}