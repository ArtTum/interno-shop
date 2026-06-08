<?php

namespace App\Repositories\Style;

use App\Repositories\BaseRepository;
use App\Models\Style;

class StyleRepository extends BaseRepository
{
   public function __construct(Style $model)
    {
        $this->model = $model;
    }
}