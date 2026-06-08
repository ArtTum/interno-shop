<?php

namespace App\Repositories\ProductRelatedProduct;

use App\Constants\ProductConstants;
use App\Models\ProductRelatedProduct;
use App\Repositories\BaseRepository;
use App\Repositories\ProductRelatedProduct\Interfaces\ProductRelatedProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductRelatedProductRepository extends BaseRepository implements ProductRelatedProductRepositoryInterface
{
    public function __construct(ProductRelatedProduct $model)
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

    public function deleteByProductAndRelatedProductIds(int $productId, array $relatedProductIds): bool
    {
        return $this->model->where('product_id', $productId)->whereIn('related_product_id', $relatedProductIds)->delete();
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }

    public function getExtraProductIdsRequired(int $productId)
    {
        return $this->model->select('related_product_id')
            ->where('product_id', $productId)
            ->where('type', ProductConstants::RELATED_PRODUCT_TYPES['extra_products'])
            ->where('required_extra', true)
            ->pluck('related_product_id')
            ->toArray();
    }
}
