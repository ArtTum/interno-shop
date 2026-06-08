<?php

namespace App\Repositories\Country;

use App\Repositories\BaseRepository;
use App\Models\Country;

class CountryRepository extends BaseRepository
{
   public function __construct(Country $model)
    {
        $this->model = $model;
    }
}