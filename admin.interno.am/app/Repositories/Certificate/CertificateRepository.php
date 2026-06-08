<?php

namespace App\Repositories\Certificate;

use App\Repositories\BaseRepository;
use App\Models\Certificate;

class CertificateRepository extends BaseRepository
{
   public function __construct(Certificate $model)
    {
        $this->model = $model;
    }
}