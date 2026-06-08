<?php

namespace App\Repositories\ProductVariantPrice;

use App\Repositories\BaseRepository;
use App\Models\ProductVariantPrice;
use Illuminate\Support\Facades\DB;

class ProductVariantPriceRepository extends BaseRepository
{
    public function __construct(ProductVariantPrice $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByVariantId(int $variantId)
    {
        return $this->model->where('product_variant_id', $variantId)->delete();
    }

    public function bulkDeleteByField(string $whereField, array $whereValues)
    {
        return $this->model->whereIn($whereField, $whereValues)->delete();
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }

    public function fetchByFieldAlt(string $whereField, string $whereValue, string $select)
    {
        return $this->model->select(DB::raw($select))
            ->where($whereField, $whereValue)
            ->get();
    }

    public function fetchPricesForFront(int $variantId, float $rate, ?int $customerGroupId)
    {
        return $this->model->select('min', DB::raw("price * {$rate} as price"))
            ->where('product_variant_id', $variantId)
            ->where('customer_group_id', $customerGroupId)
            ->orderBy('min', 'desc')
            ->get();
    }
}
