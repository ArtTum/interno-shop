<?php

namespace App\Repositories\Author;

use App\Repositories\BaseRepository;
use App\Models\Author;

class AuthorRepository extends BaseRepository
{
   public function __construct(Author $model)
    {
        $this->model = $model;
    }
}