<?php

namespace App\Repositories\ColourTemperature;

use App\Repositories\BaseRepository;
use App\Models\ColourTemperature;

class ColourTemperatureRepository extends BaseRepository
{
   public function __construct(ColourTemperature $model)
    {
        $this->model = $model;
    }
}