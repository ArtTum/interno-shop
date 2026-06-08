<?php

namespace App\Repositories\InstallationLocation;

use App\Repositories\BaseRepository;
use App\Models\InstallationLocation;

class InstallationLocationRepository extends BaseRepository
{
   public function __construct(InstallationLocation $model)
    {
        $this->model = $model;
    }
}