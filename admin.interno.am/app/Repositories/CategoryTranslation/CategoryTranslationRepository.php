<?php

namespace App\Repositories\CategoryTranslation;

use App\Models\CategoryTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\CategoryTranslation\Interfaces\CategoryTranslationRepositoryInterface;
use App\Services\General\CustomSlugService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoryTranslationRepository extends BaseRepository implements CategoryTranslationRepositoryInterface
{
    public function __construct(CategoryTranslation $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        $data = CustomSlugService::setPathBySlugCategory($data, 'catalog');
        return parent::create($data);
    }

    public function insert(array $data): bool
    {
        $data = CustomSlugService::setPathBySlugCategory($data, 'catalog');
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByCategoryAndLanguageId(int $categoryId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('category_id', $categoryId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        $data = CustomSlugService::setPathBySlugCategory($data, 'catalog');

        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchForMenuName(int $id)
    {
        return $this->model->select('name')
            ->where('id', $id)
            ->value('name');
    }

    public function getPathByCategoryIdAndLanguageId(int $categoryId, int $languageId)
    {
        return $this->model->select('id', 'path')->where('category_id', $categoryId)->where('language_id', $languageId)->value('path');
    }

    public function getWithAllDescendants(int $languageId): Collection
    {
        return $this->model->select('id', 'parent_id', 'name')
            ->where('language_id', $languageId)
            ->whereNull('parent_id')
            ->with([
                'allDescendants' => function ($allDescendantsQuery) use ($languageId) {
                    $allDescendantsQuery->select('id', 'parent_id', 'name');
                }
            ])
            ->get();
    }

    public function getIdByCategoryAndLanguage(int $categoryId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('category_id', $categoryId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getCategoryIdIBySlugAndLanguage(string $slug, int $languageId): ?int
    {
        return $this->model->select('category_id')
            ->where('slug', $slug)
            ->where('language_id', $languageId)
            ->value('category_id');
    }

    public function getLanguageIdsByCategoryId(int $categoryId): array
    {
        return $this->model->select('language_id')
            ->where('category_id', $categoryId)
            ->pluck('language_id')
            ->toArray();
    }

    public function getIdByCategoryIdAndLanguageId(int $categoryId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('category_id', $categoryId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getTranslationByCategoryIdAndLanguageId(int $categoryId, int $languageId)
    {
        return $this->model->select('*')
            ->where('category_id', $categoryId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getForHierarchy(int $id)
    {
        return $this->model->select('id', 'parent_id', 'category_id')
            ->where('id', $id)
            ->with([
                'children' => function ($query) {
                    $query->select('id', 'parent_id', 'category_id');
                }
            ])
            ->first();
    }

    public function fetchForFront(string $path, int $languageId)
    {
        return $this->model->select('id', 'parent_id', 'category_id', 'a_plus_content_id', 'snippet_id', 'name', 'path', 'breadcrumb', 'description',
            'meta_title', 'meta_description', 'meta_keywords')
            ->where('path', rtrim($path, '/'))
            ->where('language_id', $languageId)
            ->whereHas('category', function ($categoryQuery) {
                $categoryQuery->where('hide_for_front', false);
            })
            ->with([
                'children' => function ($query) {
                    $query->select('id', 'parent_id', 'category_id');
                },
                'category' => function ($query) use ($languageId) {
                    $query->select('id', 'calculator_id', 'media_id', 'responsive_settings', 'products_showing_type')
                        ->with([
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'original_path AS path', 'alt', 'height', 'width'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            },
                            'calculator' => function ($query) use ($languageId) {
                                $query->select('id')
                                    ->whereHas('calculator_translation', function ($query) use ($languageId) {
                                        $query->select('id', 'calculator_id')->where('language_id', $languageId);
                                    })
                                    ->with([
                                        'calculator_translation' => function ($query) use ($languageId) {
                                            $query->select('calculator_id', 'name', 'config')->where('language_id', $languageId);
                                        }
                                    ]);
                            }
                        ]);
                }
            ])
            ->first();
    }

    public function fetchForSEOAlternatives(int $categoryId)
    {
        return $this->model->select('path', 'hreflang', 'languages.code', 'default_hreflang')
            ->join('languages', 'languages.id', '=', 'category_translations.language_id')
            ->where('category_id', $categoryId)
            ->get();
    }

    public function getCategoryTranslations()
    {
        return $this->model->select('id', 'slug', 'name', 'language_id', 'path', 'category_id')
            ->with([
                'category' => function ($query) {
                    $query->select('id', 'parent_id');
                }
            ])
            ->whereHas('category')
            ->get()
            ->toArray();
    }

    public function generatePathForPage(int $id)
    {
        return $this->model->select('id', 'slug', 'parent_id', 'name')
            ->where('id', $id)
            ->first()?->getSlugPathWithBreadcrumb();
    }

    public function getCategoryIdById(int $id)
    {
        return $this->model->select('category_id')
            ->where('id', $id)
            ->value('category_id');
    }

    public function getPathsForCacheClearing(int $productId, int $languageId)
    {
        return $this->model->select('path')
            ->join('languages', 'languages.id', '=', 'category_translations.language_id')
            ->where('category_id', $productId)
            ->when($languageId > -1, function ($query) use ($languageId) {
                $query->where('languages.id', $languageId);
            })
            ->get();
    }

    public function fetchPathForChangeLanguage(int $languageId, int $id)
    {
        return $this->model->select('path')
            ->where('language_id', $languageId)
            ->whereHas('category', function ($q) use ($id) {
                $q->whereHas('category_translation', function ($q) use ($id) {
                    $q->where('id', $id);
                });
            })
            ->value('path');
    }

    public function getNeededTranslationPathByParams(string $path, int $languageId)
    {
        return $this->model->select('path')
            ->whereHas('category', function ($q) use ($path) {
                $q->whereHas('category_translation', function ($q) use ($path) {
                    $q->where('path', $path);
                });
            })
            ->where('language_id', $languageId)
            ->value('path');
    }

    public function getNeededTranslationIdByParams(int $translationId, int $languageId)
    {
        return $this->model->select('id')
            ->whereHas('category', function ($q) use ($translationId) {
                $q->whereHas('category_translation', function ($q) use ($translationId) {
                    $q->where('id', $translationId);
                });
            })
            ->where('language_id', $languageId)
            ->value('id');
    }
}
