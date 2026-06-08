<?php

namespace App\Repositories\NewsletterInfo;

use App\Repositories\BaseRepository;
use App\Models\NewsletterInfo;

class NewsletterInfoRepository extends BaseRepository
{
   public function __construct(NewsletterInfo $model)
    {
        $this->model = $model;
    }
}