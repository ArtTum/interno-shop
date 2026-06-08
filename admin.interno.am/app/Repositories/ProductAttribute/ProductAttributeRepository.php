<?php

namespace App\Repositories\ProductAttribute;

use App\Models\ProductAttribute;
use App\Repositories\BaseRepository;
use App\Repositories\ProductAttribute\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeRepository extends BaseRepository implements ProductAttributeRepositoryInterface
{
    public function __construct(ProductAttribute $model)
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
        return $this->model->select('attribute_id')->where('product_id', $productId)->pluck('attribute_id')->toArray();
    }

    public function deleteByProductAndAttributeIds(int $productId, array $attributeIds): bool
    {
        return $this->model->where('product_id', $productId)->whereIn('attribute_id', $attributeIds)->delete();
    }

    public function deleteByProductId(int $productId): bool
    {
        return $this->model->where('product_id', $productId)->delete();
    }
}
