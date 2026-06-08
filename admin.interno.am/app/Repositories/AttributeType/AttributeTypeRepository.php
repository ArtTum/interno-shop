<?php

namespace App\Repositories\AttributeType;

use App\Constants\AttributeConstants;
use App\Models\AttributeType;
use App\Repositories\AttributeType\Interfaces\AttributeTypeRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AttributeTypeRepository extends BaseRepository implements AttributeTypeRepositoryInterface
{
    public function __construct(AttributeType $model)
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
            ->when(isset($params['for_attributes']) && $params['for_attributes'], function ($query) use ($params) {
                $query->where('language_id', $params['base_language_id']);
            })
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('attribute_type_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('attribute_type_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->with([
                'attribute_type_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'attribute_type_id', 'name', 'slug', 'label')
                        ->where('language_id', $baseLanguageId);
                }
            ])
            ->withCount('attributes')
            ->get();
    }

    public function fetchForProduct(int $languageId, ?int $productId = null): Collection
    {
        return $this->model->select('id', 'logic')
            ->whereHas('attributes')
            ->whereHas('attribute_type_translation')
            ->with([
                'attribute_type_translation' => function ($translationQuery) use ($languageId, $productId) {
                    $translationQuery->select('id', 'attribute_type_id', 'name', 'slug')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                },
                'attributes' => function ($attributeQuery) use ($languageId, $productId) {
                    $attributeQuery->select('attributes.id', 'attributes.attribute_type_id', 'attributes.media_id', 'attribute_translations.value')
                        ->join('attribute_types', 'attribute_types.id', '=', 'attributes.attribute_type_id')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc')
                        ->with([
                            'product_attribute' => function ($q) use ($productId) {
                                $q->select('attribute_id', 'product_id')
                                    ->where('product_id', $productId);
                            },
                            'media' => function ($imageQuery) {
                                $imageQuery->select([
                                    'id', 'original_path AS path'
                                ]);
                            }
                        ]);
                }
            ])
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
    public function fetchForProductAnalytics(int $languageId, int $productId): Collection
    {
        return $this->model->select('id')
            ->whereHas('attributes', function ($q) use ($productId) {
                $q->whereHas('product_attribute', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            })
            ->whereHas('attribute_type_translation')
            ->with([
                'attribute_type_translation' => function ($translationQuery) use ($languageId, $productId) {
                    $translationQuery->select('id', 'attribute_type_id', 'name')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                },
                'attributes' => function ($attributeQuery) use ($languageId, $productId) {
                    $attributeQuery->select('attributes.id as value', 'attributes.attribute_type_id', 'attribute_translations.value as label')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->orderBy('priority', 'asc')
                        ->orderBy('attributes.id', 'asc')
                        ->whereHas('product_attribute', function ($q) use ($productId) {
                            $q->where('product_id', $productId);
                        });
                }
            ])
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchForGenerationVariations(int $productId)
    {
        return $this->model->select('id')
            ->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation'])
            ->whereHas('attributes', function ($query) use ($productId) {
                $query->whereHas('product_attribute', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                });
            })
            ->with([
                'attributes' => function ($attributeQuery) use ($productId) {
                    $attributeQuery->select('attributes.id', 'attributes.attribute_type_id')
                        ->join('attribute_types', 'attribute_types.id', '=', 'attributes.attribute_type_id')
                        ->when($productId, function ($query) use ($productId) {
                            $query->join('product_attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
                                ->where('product_attributes.product_id', $productId);
                        });
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchForAttributestypesTranslations(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'attribute_types.id', 'id');

        return $fullData;
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'attribute_type_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'attribute_type_id', 'name', 'description', 'slug', 'label', 'approved')->where('language_id', $languageId);
                }
            ])
            ->withCount('attributes')
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model->with([
            'attribute_type_translation' => function ($translationQuery) use ($data) {
                $translationQuery->select('attribute_type_id', 'name', 'slug', 'label')
                    ->where('language_id', $data['language_id']);
            },
        ])->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
            $query->whereIn('id', $data['ids'])
                ->orderBy($data['ordering_field'], $data['ordering_direction']);;
        })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchForProductDetail(int $languageId, int $productId): Collection
    {
        return $this->model->select('attribute_types.id', 'logic', 'name', 'slug', 'label', 'type', 'attribute_types.is_conditional', 'attribute_types.bigger_values')
            ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
            ->where('attribute_type_translations.language_id', $languageId)
            ->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation'])
            ->whereHas('attributes', function ($query) use ($productId) {
                $query->whereHas('product_attribute', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                });
            })
            ->with([
                'attributes' => function ($attributeQuery) use ($languageId, $productId) {
                    $attributeQuery->select('attributes.attribute_type_id', 'attributes.media_id', 'attribute_translations.slug',
                        'attribute_translations.value', 'attribute_translations.description', 'attributes.id', 'attributes.priority')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->join('product_attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
                        ->where('product_attributes.product_id', $productId)
                        ->whereHas('product_variant', function ($query) use ($productId) {
                            $query->where('product_id', $productId)->where('status', true);
                        })
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc')
                        ->with([
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'path', 'original_path', 'alt', 'width', 'height'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            }
                        ]);
                }
            ])
            ->orderBy('is_conditional', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchForNotifyOutOfStockVariants(int $languageId, int $productId)
    {
        return $this->model->select('attribute_types.id', 'name', 'slug', 'attribute_types.is_conditional')
            ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
            ->where('attribute_type_translations.language_id', $languageId)
            ->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation'])
            ->whereHas('attributes', function ($query) use ($productId) {
                $query->whereHas('product_variant', function ($query) use ($productId) {
                        $query->where('product_id', $productId)->where('stock_status', false);
                    });
            })
            ->with([
                'attributes' => function ($attributeQuery) use ($languageId, $productId) {
                    $attributeQuery->select('attributes.attribute_type_id', 'attribute_translations.slug',
                        'attribute_translations.value', 'attributes.id', 'attributes.priority')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->whereHas('product_variant', function ($query) use ($productId) {
                            $query->where('product_id', $productId)->where('stock_status', false);
                        })
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc');
                }
            ])
            ->orderBy('is_conditional', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->first();
    }

    public function fetchForProductDetailExtraProduct(int $languageId, int $productId): Collection
    {
        return $this->model->select('attribute_types.id', 'name', 'label', 'is_conditional', 'type', 'bigger_values')
            ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
            ->where('attribute_type_translations.language_id', $languageId)
            ->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation'])
            ->whereHas('attributes', function ($query) use ($productId) {
                $query->whereHas('product_attribute', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                });
            })
            ->with([
                'attributes' => function ($attributeQuery) use ($languageId, $productId) {
                    $attributeQuery->select(
                        'attributes.attribute_type_id', 'attribute_translations.value', 'attributes.id', 'attributes.media_id',
                    )
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->join('product_attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
                        ->where('product_attributes.product_id', $productId)
                        ->whereHas('product_variant', function ($query) use ($productId) {
                            $query->where('product_id', $productId)->where('status', true);
                        })
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc')
                        ->with([
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'path', 'alt', 'width', 'height'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            }
                        ]);
                }
            ])
            ->orderBy('is_conditional', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchForFrontFilters(int $languageId, ?int $categoryId)
    {
        return $this->model->select('attribute_types.id', 'slug', 'name')
            ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
            ->where('language_id', $languageId)
            ->where('is_filterable', true)
            ->whereHas('attributes', function ($query) use ($languageId) {
                $query->whereHas('product_attribute');
            })
            ->with([
                'attributes' => function ($attributeQuery) use ($languageId, $categoryId) {
                    $attributeQuery->select('attributes.attribute_type_id', 'attribute_translations.value', 'attribute_translations.slug',
                        'color_code')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->when($categoryId, function ($query) use ($categoryId, $languageId) {
                            $query->whereHas('products', function ($query) use ($categoryId) {
                                $query->whereHas('categories_pivot', function ($query) use ($categoryId) {
                                    $query->where('category_id', $categoryId);
                                });
                            });
                        })
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc');
                }
            ])
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}
