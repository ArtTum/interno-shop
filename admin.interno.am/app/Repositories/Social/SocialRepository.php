<?php

namespace App\Repositories\Social;

use App\Repositories\BaseRepository;
use App\Models\Social;

class SocialRepository extends BaseRepository
{
   public function __construct(Social $model)
    {
        $this->model = $model;
    }
}