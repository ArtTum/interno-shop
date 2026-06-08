<?php

namespace App\Repositories\ProductMultiselectOptionTranslation;

use App\Repositories\BaseRepository;
use App\Models\ProductMultiselectOptionTranslation;

class ProductMultiselectOptionTranslationRepository extends BaseRepository
{
    public function __construct(ProductMultiselectOptionTranslation $model)
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

    public function fetchByLanguageIdAndIds(array $ids, int $languageId)
    {
        return $this->model->select('title')
            ->whereIn('product_multiselect_option_id', $ids)
            ->where('language_id', $languageId)
            ->pluck('title')
            ->toArray();
    }
}
