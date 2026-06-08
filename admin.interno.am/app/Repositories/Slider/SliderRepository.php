<?php

namespace App\Repositories\Slider;

use App\Repositories\BaseRepository;
use App\Models\Slider;

class SliderRepository extends BaseRepository
{
   public function __construct(Slider $model)
    {
        $this->model = $model;
    }
}