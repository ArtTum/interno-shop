<?php

namespace App\Repositories\MobileMenu;

use App\Repositories\BaseRepository;
use App\Models\MobileMenu;

class MobileMenuRepository extends BaseRepository
{
   public function __construct(MobileMenu $model)
    {
        $this->model = $model;
    }
}