<?php

namespace App\Repositories\ProductCategory;

use App\Models\ProductCategory;
use App\Repositories\BaseRepository;
use App\Repositories\ProductCategory\Interfaces\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    public function __construct(ProductCategory $model)
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

    public function fetchIdsByProductId(int $productId): array
    {
        return $this->model->select('category_id')->where('product_id', $productId)->pluck('category_id')->toArray();
    }

    public function deleteByProductAndCategoryIds(int $productId, array $categoryIds): bool
    {
        return $this->model->where('product_id', $productId)->whereIn('category_id', $categoryIds)->delete();
    }

    public function updatePrimaryStatus(int $productId)
    {
        return $this->model->where('product_id', $productId)->update(['is_primary' => false]);
    }

    public function updatePrimaryStatusByCategoryId(int $productId, int $categoryId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('category_id', $categoryId)
            ->update(['is_primary' => true]);
    }
}
