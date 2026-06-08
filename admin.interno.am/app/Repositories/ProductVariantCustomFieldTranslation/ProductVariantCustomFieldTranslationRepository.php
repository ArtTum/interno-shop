<?php

namespace App\Repositories\ProductVariantCustomFieldTranslation;

use App\Models\ProductVariantCustomFieldTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\ProductVariantCustomFieldTranslation\Interfaces\ProductVariantCustomFieldTranslationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductVariantCustomFieldTranslationRepository extends BaseRepository implements ProductVariantCustomFieldTranslationRepositoryInterface
{
    public function __construct(ProductVariantCustomFieldTranslation $model)
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

    public function bulkDelete(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function fetchKeysByDistinct()
    {
        return $this->model->select('key')
            ->distinct('key')
            ->pluck('key')
            ->toArray();
    }

    public function deleteByParams(int $variantId, int $languageId)
    {
        return $this->model
            ->where('product_variant_id', $variantId)
            ->where('language_id', $languageId)
            ->delete();
    }
}
