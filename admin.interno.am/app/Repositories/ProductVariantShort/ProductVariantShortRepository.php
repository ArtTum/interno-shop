<?php

namespace App\Repositories\ProductVariantShort;

use App\Repositories\BaseRepository;
use App\Models\ProductVariantShort;
use Illuminate\Database\Eloquent\Model;

class ProductVariantShortRepository extends BaseRepository
{
    public function __construct(ProductVariantShort $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }
}
