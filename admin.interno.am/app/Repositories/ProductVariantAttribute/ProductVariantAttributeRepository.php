<?php

namespace App\Repositories\ProductVariantAttribute;

use App\Models\ProductVariantAttribute;
use App\Repositories\BaseRepository;
use App\Repositories\ProductVariantAttribute\Interfaces\ProductVariantAttributeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductVariantAttributeRepository extends BaseRepository implements ProductVariantAttributeRepositoryInterface
{
    public function __construct(ProductVariantAttribute $model)
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

    public function detectNeededAttributeValues(int $productId, array $ids)
    {
        return $this->model->select('attribute_id')
            ->when(!empty($ids), function ($q) use ($productId, $ids) {
                $q->whereHas('product_variant', function ($query) use ($productId, $ids) {
                    $query->where('product_id', $productId)
                        ->where('status', true);

                    foreach ($ids as $attributeId) {
                        $query->whereHas('product_variant_attributes', function ($q) use ($attributeId) {
                            $q->where('attribute_id', $attributeId);
                        });
                    }
                });
            })
            ->groupBy('attribute_id')
            ->pluck('attribute_id')
            ->toArray();
    }
}
