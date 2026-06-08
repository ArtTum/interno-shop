<?php

namespace App\Repositories\AttributeTranslation;

use App\Constants\AttributeConstants;
use App\Models\AttributeTranslation;
use App\Repositories\AttributeTranslation\Interfaces\AttributeTranslationRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AttributeTranslationRepository extends BaseRepository implements AttributeTranslationRepositoryInterface
{
    public function __construct(AttributeTranslation $model)
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

    public function getAttributeValueInfoBySlugAndLanguage(string $slug, int $languageId)
    {
        return $this->model->select('attribute_id', 'value')
            ->where('slug', $slug)
            ->where('language_id', $languageId)
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchByAttributeAndLanguageId(int $attributeId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('attribute_id', $attributeId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getTranslationByAttributeIdAndLanguageId(int $attributeId, int $languageId)
    {
        return $this->model->select('*')
            ->where('attribute_id', $attributeId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getAttributeIdsBySlugsForCatalog(array $slugs): array
    {
        return $this->model->select('attribute_id')
            ->where(function ($query) use ($slugs) {
                $query->whereIn('slug', $slugs)
                    ->whereHas('attribute', function ($query) use ($slugs) {
                        $query->select('id', 'attribute_type_id')
                            ->orWhereIn('color_code', $slugs);
                    });
            })
            ->whereHas('attribute', function ($query) use ($slugs) {
                $query->select('id', 'attribute_type_id')
                    ->whereHas('attribute_type', function ($query) {
                        $query->select('id')
                            ->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation'])
                            ->where('is_filterable', true);;
                    });
            })
            ->pluck('attribute_id')
            ->toArray();
    }

    public function getAttributeIds(array $slugs)
    {
        return $this->model->select('attribute_id')
            ->orWhereIn('slug', $slugs)
            ->orWhereIn('color_code', $slugs)
            ->pluck('attribute_id')
            ->toArray();
    }
}
