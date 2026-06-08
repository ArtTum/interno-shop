<?php

namespace App\Repositories\ProductVariantReel;

use App\Repositories\BaseRepository;
use App\Models\ProductVariantReel;
use Illuminate\Database\Eloquent\Model;

class ProductVariantReelRepository extends BaseRepository
{
    public function __construct(ProductVariantReel $model)
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

    public function checkReelMediaId(int $mediaId, int $productVariantId): ?bool
    {
        return $this->model
            ->where('media_id', $mediaId)
            ->where('product_variant_id', $productVariantId)
            ->exists();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function deleteByProductVariantId(int $productVariantId): bool
    {
        return $this->model->where('product_variant_id', $productVariantId)->delete();
    }
}
