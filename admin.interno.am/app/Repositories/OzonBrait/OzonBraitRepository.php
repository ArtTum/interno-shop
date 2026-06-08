<?php

namespace App\Repositories\OzonBrait;

use App\Repositories\BaseRepository;
use App\Models\OzonBrait;

class OzonBraitRepository extends BaseRepository
{
   public function __construct(OzonBrait $model)
    {
        $this->model = $model;
    }
}