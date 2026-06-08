<?php

namespace App\Repositories\Faq;

use App\Repositories\BaseRepository;
use App\Models\Faq;

class FaqRepository extends BaseRepository
{
   public function __construct(Faq $model)
    {
        $this->model = $model;
    }
}