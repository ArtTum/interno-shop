<?php

namespace App\Repositories\Article;

use App\Repositories\BaseRepository;
use App\Models\Article;

class ArticleRepository extends BaseRepository
{
   public function __construct(Article $model)
    {
        $this->model = $model;
    }
}