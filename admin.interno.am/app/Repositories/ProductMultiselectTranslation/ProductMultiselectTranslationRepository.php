<?php

namespace App\Repositories\ProductMultiselectTranslation;

use App\Repositories\BaseRepository;
use App\Models\ProductMultiselectTranslation;

class ProductMultiselectTranslationRepository extends BaseRepository
{
    public function __construct(ProductMultiselectTranslation $model)
    {
        $this->model = $model;
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }

    public function deleteByParams(array $whereParams)
    {
        return $this->model->where($whereParams)->delete();
    }
}
