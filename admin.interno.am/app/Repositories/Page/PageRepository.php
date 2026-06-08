<?php

namespace App\Repositories\Page;

use App\Repositories\BaseRepository;
use App\Models\Page;

class PageRepository extends BaseRepository
{
   public function __construct(Page $model)
    {
        $this->model = $model;
    }
}