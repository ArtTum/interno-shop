<?php

namespace App\Repositories\ValidVatNumber;

use App\Repositories\BaseRepository;
use App\Models\ValidVatNumber;

class ValidVatNumberRepository extends BaseRepository
{
    public function __construct(ValidVatNumber $model)
    {
        $this->model = $model;
    }

    public function checkExists(string $vatNumber)
    {
        return $this->model->where('number', $vatNumber)->exists();
    }

    public function updateOrCreate(array $checkingData, array $data)
    {
        return $this->model->updateOrCreate($checkingData, $data);
    }
}
