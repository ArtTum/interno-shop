<?php

namespace App\Repositories\Attribute;

use App\Constants\AttributeConstants;
use App\Models\Attribute;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AttributeRepository extends BaseRepository
{
    public function __construct(Attribute $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function recover(int $id): bool
    {
        return $this->model->onlyTrashed()->find($id)->restore();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['attribute_type_id'] >= 0, function ($statusQuery) use ($params) {
                $statusQuery->where('attribute_type_id', $params['attribute_type_id']);
            })
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('attribute_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('attribute_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'attribute_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'attribute_id', 'value', 'slug')->where('language_id', $baseLanguageId);
                },
                'attribute_type' => function ($parentQuery) use ($params) {
                    $parentQuery->select('id')
                        ->with([
                            'attribute_type_translation' => function ($translationQuery) use ($params) {
                                $baseLanguageId = $params['base_language_id'];
                                $translationQuery->select('id', 'attribute_type_id', 'name')->where('language_id', $baseLanguageId);
                            }
                        ]);
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                }
            ])
            ->withCount(['attribute_variants_pivot', 'product_attributes_pivot'])
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchForAttributesTranslations(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'attributes.id', 'id');

        return $fullData;
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
        return parent::delete($id);
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId)
    {
        return $this->model->select(DB::raw($selectedFields))
            ->with([
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->where($whereField, $whereValue)
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'attribute_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'attribute_id', 'language_id', 'slug', 'value', 'description', 'approved')->where('language_id', $languageId);
                    }
                ]);
            })
            ->withCount(['attribute_variants_pivot'])
            ->first();
    }

    public function fetchByWatermarkImage(string $whereField, string|int $whereValue, string $selectedFields)
    {
        return $this->model->select(DB::raw($selectedFields))
            ->with([
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->where($whereField, $whereValue)
            ->first();
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model->with([
            'attribute_translation' => function ($translationQuery) use ($data) {
                $translationQuery->select('attribute_id', 'value', 'slug', 'description')
                    ->where('language_id', $data['language_id']);
            },
            'media' => function ($imageQuery) {
                $imageQuery->select('id', 'original_path');
            }
        ])->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
            $query->whereIn('id', $data['ids'])
                ->orderBy($data['ordering_field'], $data['ordering_direction']);;
        })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function existsById(int $id): bool
    {
        return $this->model->select('id')
            ->where('id', $id)
            ->exists();
    }

    public function getAttributesBySlugs(array $slugs, int $languageId)
    {
        return $this->model->select('value', 'attribute_id', 'slug', 'attribute_type_id')
            ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
            ->whereIn('slug', $slugs)
            ->where('language_id', $languageId)
            ->whereHas('attribute_type', function ($query) {
                $query->select('id')->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation']);
            })
            ->with([
                'attribute_type' => function ($query) use ($languageId) {
                    $query->select('attribute_types.id', 'slug', 'name')
                        ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                        ->where('attribute_type_translations.language_id', $languageId);
                }
            ])
            ->get();
    }

    public function getAttributeIdsBySlugs(array $slugs, int $languageId)
    {
        return $this->model->select('attribute_id')
            ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
            ->whereIn('slug', $slugs)
            ->where('language_id', $languageId)
            ->whereHas('attribute_type', function ($query) {
                $query->select('id')->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation']);
            })
            ->pluck('attribute_id')
            ->toArray();
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function checkUniqingByAttributeType(int $attributeTypeId, string $name, int $languageId, ?int $currentId): bool
    {
        return $this->model->select('attributes.id')
            ->join('attribute_types', 'attribute_types.id', '=', 'attributes.attribute_type_id')
            ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
            ->when($currentId, function ($query) use ($currentId) {
                $query->where('attribute_translations.id', '!=', $currentId);
            })
            ->where('attribute_type_id', $attributeTypeId)
            ->where('language_id', $languageId)
            ->where('value', $name)
            ->exists();
    }
}
