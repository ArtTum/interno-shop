<?php

namespace App\Repositories\PageTranslation;

use App\Constants\PageConstants;
use App\Constants\ProductConstants;
use App\Models\PageTranslation;
use App\Repositories\BaseRepository;
use App\Traits\Contents\ContentTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageTranslationRepository extends BaseRepository
{
    use ContentTrait;

    public function __construct(PageTranslation $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function getAllPages(int $languageId, ?bool $withHome = false): Collection
    {
        return $this->model->select('id', 'page_id', 'parent_id', 'name')
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) use ($withHome) {
                $query->where('type', PageConstants::PAGE_TYPES['page'])
                    ->when(!$withHome, function ($query) {
                        $query->where('is_home', false);
                    });
            })
            ->whereNull('parent_id')
            ->get();
    }

    public function getWithAllDescendants(int $languageId, ?bool $withHome = false): Collection
    {
        return $this->model->select('id', 'parent_id', 'name')
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) use ($withHome) {
                $query->where('type', PageConstants::PAGE_TYPES['page'])
                    ->when(!$withHome, function ($query) {
                        $query->where('is_home', false);
                    });
            })
            ->whereNull('parent_id')
            ->with([
                'allDescendants' => function ($allDescendantsQuery) use ($languageId) {
                    $allDescendantsQuery->select('id', 'parent_id', 'name');
                }
            ])
            ->get();
    }

    public function getByType(int $languageId, string $type): Collection
    {
        return $this->model->select('id as value', 'name as label')
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) use ($type) {
                $query->where('type', PageConstants::PAGE_TYPES[$type]);
            })
            ->get();
    }

    public function generatePathForPage(int $id)
    {
        return $this->model->select('id', 'slug', 'parent_id', 'name')
            ->where('id', $id)
            ->first()?->getSlugPathWithBreadcrumb();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
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

    public function getIdByPageIdAndLanguageId(int $pageId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('page_id', $pageId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getContentsByTypeAsParam(int $languageId, ?int $type): Collection
    {
        return $this->model->select('id as value', 'name as label')
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) {
                $query->where('type', PageConstants::PAGE_TYPES['a_plus_content']);
            })
            ->when(!is_null($type), function ($query) use ($type) {
                $query->whereHas('page', function ($query) use ($type) {
                    $query->where('a_plus_content_type', $type);
                });
            })
            ->get();
    }

    public function fetchForFront(int $languageId, string $path, float $rate, $b2b, $userLevelId = null)
    {
//        DB::enableQueryLog();
        return $this->model->select('id', 'page_id', 'post_category_translation_id', 'name', 'subname', 'parent_id',
            'meta_title', 'meta_description', 'meta_keywords', 'path', 'breadcrumb', 'created_at', 'updated_at')
            ->where('language_id', $languageId)
            ->where('path', rtrim($path, '/'))
            ->with([
                'page' => function ($query) use ($languageId) {
                    $query->select('id', 'media_id', 'published_at', 'type')
                        ->with([
                            'media' => function ($query) use ($languageId) {
                                $query->select('media.id', 'media.original_path as path', 'width', 'height', 'alt', 'path as general_path')
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            }
                        ]);
                },
                'sections' => function ($query) use ($languageId, $rate) {
                    return $this->sectionQuery($query, $languageId, $rate);
                }
            ])
            ->when($b2b, function ($query) {
                $query->where(function ($q) {
                    $q->orWhereHas('page', function ($q) {
                        $q->whereDoesntHave('customer_groups_pivot');
                    })
                        ->when(Auth::user()?->customer_group_id, function ($qw) {
                            $qw->orWhereHas('page', function ($q) {
                                $q->whereHas('customer_groups_pivot', function ($q) {
                                    $q->where('customer_group_id', Auth::user()->customer_group_id);
                                });
                            });
                        });
                });
            })
            ->when($userLevelId, function ($query) use ($userLevelId) {
                $query->where(function ($q) use ($userLevelId) {
                    $q->orWhereHas('page', function ($q) {
                        $q->whereDoesntHave('user_levels_pivot');
                    })->orWhereHas('page', function ($q) use ($userLevelId) {
                        $q->whereHas('user_levels_pivot', function ($q) use ($userLevelId) {
                            $q->where('user_level_id', $userLevelId);
                        });
                    });

                });
            })
            ->whereHas('page', function ($query) {
                $query->select('id')
                    ->where('status', true)
                    ->where('type', '!=', PageConstants::PAGE_TYPES['a_plus_content']);
            })
            ->first();
    }

    public function fetchAPlusContentForBuilder(int $pageTranslationId, int $languageId, float $rate)
    {
        return $this->model->select('page_translations.id', 'name', 'button_text', 'pages.type')
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->where('page_translations.id', $pageTranslationId)
            ->where('pages.status', true)
            ->with([
                'sections' => function ($query) use ($languageId, $rate) {
                    return $this->sectionQuery($query, $languageId, $rate);
                }
            ])
            ->first();
    }

    public function fetchForSEOAlternatives(int $pageId)
    {
        return $this->model->select('path', 'hreflang', 'languages.code', 'default_hreflang')
            ->join('languages', 'languages.id', '=', 'page_translations.language_id')
            ->where('page_id', $pageId)
            ->get();
    }

    public function getPostsForBlogsCount(int $languageId, ?int $postCategoryId)
    {
        return self::getPostsForBlogsQuery($languageId, $postCategoryId)->count();
    }

    public function getPostsForBlogs(int $languageId, array $pagination, ?int $postCategoryId)
    {
        return self::getPostsForBlogsQuery($languageId, $postCategoryId)
            ->select('page_id', 'name', 'subname', 'button_text', 'path', 'pages.published_at', 'pages.created_at')
            ->selectRaw('COALESCE(pages.published_at, pages.created_at) AS sort_date')
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->with([
                'page' => function ($query) use ($languageId) {
                    $query->select('id', 'media_id', 'published_at')
                        ->with([
                            'media' => function ($query) use ($languageId) {
                                $query->select([
                                    'media.id', 'type', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            }
                        ]);
                }
            ])
            ->orderByDesc('sort_date')
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function getPostsForBlogsQuery(int $languageId, ?int $postCategoryId)
    {
        return $this->model
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) {
                $query->where('type', PageConstants::PAGE_TYPES['post']);
            })
            ->when($postCategoryId, function ($query, $postCategoryId) {
                $query->where('post_category_translation_id', $postCategoryId);
            });
    }

    public function getHomePageName(int $languageId)
    {
        return $this->model->select('name')
            ->where('language_id', $languageId)
            ->whereHas('page', function ($query) {
                $query->where('is_home', true);
            })
            ->value('name');
    }

    public function fetchForClone(int $id)
    {
        return $this->model
            ->where('id', $id)
            ->with([
                'sections' => function ($query) {
                    $query->select('*')
                        ->with([
                            'columns' => function ($query) {
                                $query->select('*')
                                    ->with([
                                        'components' => function ($query) {
                                            $query->select('*')
                                                ->with(['items']);
                                        }
                                    ]);
                            }
                        ]);
                }
            ])
            ->first()
            ->toArray();
    }

    public function getPageIdById(int $id)
    {
        return $this->model->select('page_id')
            ->where('id', $id)
            ->value('page_id');
    }

    public function getPageTranslations()
    {
        return $this->model->select('page_translations.id', 'slug', 'name', 'language_id', 'path', 'parent_id', 'pages.type as page_type', 'post_category_translation_id')
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->whereIn('pages.type', [PageConstants::PAGE_TYPES['post'], PageConstants::PAGE_TYPES['page']])
            ->where('is_home', false)
            ->get()
            ->toArray();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByPageAndLanguageId(int $pageId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('page_id', $pageId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function fetchPathForChangeLanguage(int $languageId, int $id)
    {
        return $this->model->select('page_translations.path')
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->where('page_translations.language_id', $languageId)
            ->where('pages.status', true)
            ->where('pages.id', $id)
            ->value('path');
    }

    public function getNeededTranslationIdByParams(int $translationId, int $languageId)
    {
        return $this->model->select('id')
            ->whereHas('page', function ($q) use ($translationId) {
                $q->whereHas('page_translation', function ($q) use ($translationId) {
                    $q->where('id', $translationId);
                });
            })
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getNeededTranslationPathByParams(string $path, int $languageId)
    {
        return $this->model->select('path')
            ->whereHas('page', function ($q) use ($path) {
                $q->whereHas('page_translation', function ($q) use ($path) {
                    $q->where('path', $path);
                });
            })
            ->where('language_id', $languageId)
            ->value('path');
    }

    public function fetchSectionsForAiTranslation(int $translationId, int $languageId = null)
    {
        return $this->model->select('id', 'page_id', 'name')
            ->where('id', $translationId)
            ->with([
                'sections' => function ($query) use ($languageId) {
                    $query->select('id', 'page_translation_id', 'parent_id', 'priority', 'responsive_settings', 'classes', 'type', 'status')
                        ->with([
                            'columns' => function ($query) use ($languageId) {
                                $query->select('id', 'page_translation_id', 'parent_id', 'priority', 'responsive_settings', 'classes', 'type', 'status')
                                    ->with([
                                        'components' => function ($query) use ($languageId) {
                                            $query->select('page_section_components.id', 'page_section_id', 'component_id', 'priority', 'config', 'component_key')
                                                ->join('components', 'components.id', '=', 'page_section_components.component_id')
                                                ->with([
                                                    'items' => function ($query) use ($languageId) {
                                                        $query->select(
                                                            'id', 'page_section_component_id', 'category_translation_id', 'post_category_translation_id', 'product_translation_id', 'page_translation_id', 'calculator_translation_id', 'media_id', 'priority', 'config'
                                                        )->orderBy('priority', 'asc');
                                                    }
                                                ])->orderBy('priority', 'asc');
                                        }
                                    ])->orderBy('priority', 'asc');
                            }
                        ])->orderBy('priority', 'asc');
                }
            ])
            ->first();
    }

    public function getTranslationByPageIdAndLanguageId(int $page_id, int $languageId)
    {
        return $this->model->select('*')
            ->where('page_id', $page_id)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getSlugByPageIdAndLanguageId(int $pageId, int $languageId): ?string
    {
        return $this->model->select('slug')
            ->where('page_id', $pageId)
            ->where('language_id', $languageId)
            ->value('slug');
    }
}
