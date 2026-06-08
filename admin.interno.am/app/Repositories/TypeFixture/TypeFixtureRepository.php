<?php

namespace App\Repositories\TypeFixture;

use App\Repositories\BaseRepository;
use App\Models\TypeFixture;

class TypeFixtureRepository extends BaseRepository
{
   public function __construct(TypeFixture $model)
    {
        $this->model = $model;
    }
}