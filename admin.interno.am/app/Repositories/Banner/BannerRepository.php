<?php

namespace App\Repositories\Banner;

use App\Repositories\BaseRepository;
use App\Models\Banner;

class BannerRepository extends BaseRepository
{
   public function __construct(Banner $model)
    {
        $this->model = $model;
    }
}