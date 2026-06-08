<?php

namespace App\Repositories\VendorOption;

use App\Repositories\BaseRepository;
use App\Models\VendorOption;

class VendorOptionRepository extends BaseRepository
{
    public function __construct(VendorOption $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function updateOrCreate(array $checkingData, array $data)
    {
        return $this->model->updateOrCreate($checkingData, $data);
    }
}
