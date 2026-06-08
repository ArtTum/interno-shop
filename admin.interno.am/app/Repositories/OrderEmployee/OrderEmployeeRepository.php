<?php

namespace App\Repositories\OrderEmployee;

use App\Repositories\BaseRepository;
use App\Models\OrderEmployee;

class OrderEmployeeRepository extends BaseRepository
{
   public function __construct(OrderEmployee $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }
}
