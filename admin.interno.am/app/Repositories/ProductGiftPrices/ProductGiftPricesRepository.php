<?php

namespace App\Repositories\ProductGiftPrices;

use App\Repositories\BaseRepository;
use App\Models\ProductGiftPrices;

class ProductGiftPricesRepository extends BaseRepository
{
    public function __construct(ProductGiftPrices $model)
    {
        $this->model = $model;
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function getPricesArray(int $productId, int $currencyId)
    {
        return $this->model->select('price')
            ->where('product_id', $productId)
            ->where('currency_id', $currencyId)
            ->pluck('price')
            ->toArray();
    }

    public function bulkDeleteByProductIdAndPrices(int $productId, array $prices)
    {
        return $this->model->where('product_id', $productId)
            ->whereIn('price', $prices)
            ->delete();
    }
}
