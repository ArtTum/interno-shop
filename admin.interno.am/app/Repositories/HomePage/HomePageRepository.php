<?php

namespace App\Repositories\HomePage;

use App\Repositories\BaseRepository;
use App\Models\HomePage;

class HomePageRepository extends BaseRepository
{
   public function __construct(HomePage $model)
    {
        $this->model = $model;
    }
}