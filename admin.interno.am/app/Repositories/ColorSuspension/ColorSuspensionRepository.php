<?php

namespace App\Repositories\ColorSuspension;

use App\Repositories\BaseRepository;
use App\Models\ColorSuspension;

class ColorSuspensionRepository extends BaseRepository
{
   public function __construct(ColorSuspension $model)
    {
        $this->model = $model;
    }
}