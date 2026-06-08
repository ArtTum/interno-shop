<?php

namespace App\Repositories\UserLevelOption;

use App\Repositories\BaseRepository;
use App\Models\UserLevelOption;

class UserLevelOptionRepository extends BaseRepository
{
   public function __construct(UserLevelOption $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
