<?php

namespace App\Repositories\Phase;

use App\Repositories\BaseRepository;
use App\Models\Phase;

class PhaseRepository extends BaseRepository
{
   public function __construct(Phase $model)
    {
        $this->model = $model;
    }
}