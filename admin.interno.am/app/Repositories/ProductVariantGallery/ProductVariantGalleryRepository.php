<?php

namespace App\Repositories\ProductVariantGallery;

use App\Models\ProductVariantGallery;
use App\Repositories\BaseRepository;
use App\Repositories\ProductVariantGallery\Interfaces\ProductVariantGalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductVariantGalleryRepository extends BaseRepository implements ProductVariantGalleryRepositoryInterface
{
    public function __construct(ProductVariantGallery $model)
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

    public function checkGalleryMediaId(int $mediaId, int $productVariantId): ?bool
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
