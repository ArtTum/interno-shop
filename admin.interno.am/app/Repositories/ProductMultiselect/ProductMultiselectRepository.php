<?php

namespace App\Repositories\ProductMultiselect;

use App\Repositories\BaseRepository;
use App\Models\ProductMultiselect;
use Illuminate\Database\Eloquent\Model;

class ProductMultiselectRepository extends BaseRepository
{
    public function __construct(ProductMultiselect $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }
}
