<?php

namespace App\Repositories\ProductVariantTranslation;

use App\Repositories\BaseRepository;
use App\Models\ProductVariantTranslation;
use App\Services\General\CustomSlugService;

class ProductVariantTranslationRepository extends BaseRepository
{
    public function __construct(ProductVariantTranslation $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        $data = CustomSlugService::setPathBySlugProduct($data, 'product');
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByVariantAndLanguageId(int $productVariantId, int $languageId): bool
    {
        return $this->model->where('product_variant_id', $productVariantId)
            ->where('language_id', $languageId)
            ->delete();
    }

    public function updateOrCreate(array $checkingData, array $data)
    {
        return $this->model->updateOrCreate($checkingData, $data);
    }
}
