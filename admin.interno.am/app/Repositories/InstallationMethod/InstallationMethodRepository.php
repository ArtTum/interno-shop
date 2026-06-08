<?php

namespace App\Repositories\InstallationMethod;

use App\Repositories\BaseRepository;
use App\Models\InstallationMethod;

class InstallationMethodRepository extends BaseRepository
{
   public function __construct(InstallationMethod $model)
    {
        $this->model = $model;
    }
}