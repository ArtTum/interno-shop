<?php

namespace App\Repositories\OzonSvetlofon;

use App\Repositories\BaseRepository;
use App\Models\OzonSvetlofon;

class OzonSvetlofonRepository extends BaseRepository
{
   public function __construct(OzonSvetlofon $model)
    {
        $this->model = $model;
    }
}