<?php

namespace App\Repositories\ProductVariantTranslationGallery;

use App\Repositories\BaseRepository;
use App\Models\ProductVariantTranslationGallery;
use Illuminate\Database\Eloquent\Model;

class ProductVariantTranslationGalleryRepository extends BaseRepository
{
    public function __construct(ProductVariantTranslationGallery $model)
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

    public function checkGalleryMediaId(int $mediaId, int $productVariantTranslationId): ?bool
    {
        return $this->model
            ->where('media_id', $mediaId)
            ->where('product_variant_translation_id', $productVariantTranslationId)
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

    public function deleteByProductVariantTranslationId(int $productVariantTranslationId): bool
    {
        return $this->model->where('product_variant_translation_id', $productVariantTranslationId)->delete();
    }
}
