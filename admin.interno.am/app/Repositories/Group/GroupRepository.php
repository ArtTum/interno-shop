<?php

namespace App\Repositories\Group;

use App\Repositories\BaseRepository;
use App\Models\Group;

class GroupRepository extends BaseRepository
{
   public function __construct(Group $model)
    {
        $this->model = $model;
    }
}