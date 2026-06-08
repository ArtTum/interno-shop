<?php

namespace App\Repositories\AttributeTypeTranslation;

use App\Models\AttributeTypeTranslation;
use App\Repositories\AttributeTypeTranslation\Interfaces\AttributeTypeTranslationRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AttributeTypeTranslationRepository extends BaseRepository implements AttributeTypeTranslationRepositoryInterface
{
    public function __construct(AttributeTypeTranslation $model)
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

    public function fetchByAttributeTypeAndLanguageId(int $attributeTypeId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('attribute_type_id', $attributeTypeId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getTranslationByAttributetypeIdAndLanguageId(int $attributeTypeId, int $languageId)
    {
        return $this->model->select('*')
            ->where('attribute_type_id', $attributeTypeId)
            ->where('language_id', $languageId)
            ->first();
    }
}
