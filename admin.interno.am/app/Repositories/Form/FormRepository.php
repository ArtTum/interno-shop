<?php

namespace App\Repositories\Form;

use App\Repositories\BaseRepository;
use App\Models\Form;

class FormRepository extends BaseRepository
{
   public function __construct(Form $model)
    {
        $this->model = $model;
    }
}