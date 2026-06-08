<?php

namespace App\Repositories\MenuTranslation;

use App\Models\CategoryTranslation;
use App\Models\MenuTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\MenuTranslation\Interfaces\MenuTranslationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuTranslationRepository extends BaseRepository implements MenuTranslationRepositoryInterface
{
    public function __construct(MenuTranslation $model)
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

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function bulkDeleteByCategoryId(int $categoryId): bool
    {
        $translationIds = CategoryTranslation::select('id')
            ->where('category_id', $categoryId)
            ->pluck('id')
            ->toArray();

        return $this->model->whereIn('category_translation_id', $translationIds)->delete();
    }

    public function updateOrInsert(array $data): bool
    {
        $menuTranslation = $this->model->select('id')
            ->where('menu_id', $data['menu_id'])
            ->where('language_id', $data['language_id'])
            ->first();

        if ($menuTranslation) {
            $menuTranslation->update([
                $data,
            ]);
        } else {
            self::insert($data);
        }

        return true;
    }

    public function fetchByMenuAndLanguageId(int $menuId, int $languageId, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where('menu_id', $menuId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getWithAllDescendants(int $languageId, int $type): Collection
    {
        return $this->model->select('id', 'parent_id', 'name')
            ->where('language_id', $languageId)
            ->where('type', $type)
            ->whereNull('parent_id')
            ->with([
                'allDescendants' => function ($allDescendantsQuery) use ($languageId) {
                    $allDescendantsQuery->select('id', 'parent_id', 'name')->orderBy('priority', 'ASC');
                }
            ])
            ->orderBy('priority', 'ASC')
            ->get();
    }

    public function getWithAllDescendantsForFront(int $languageId, $userLevelId = null): Collection
    {
        return $this->model->select('id', 'parent_id', 'type', 'category_translation_id', 'product_translation_id',
            'page_translation_id', 'name', 'url', 'new_tab', 'text_for_all')
            ->where('language_id', $languageId)
            ->where('status', true)
            ->whereNull('parent_id')
            ->with([
                'allDescendantsForFront' => function ($allDescendantsQuery) use ($languageId) {
                    $allDescendantsQuery->select('id', 'parent_id', 'type', 'category_translation_id', 'product_translation_id',
                        'page_translation_id', 'name', 'url', 'new_tab', 'text_for_all')
                        ->with(['page_translation' => function ($query) {
                                $query->select('id', 'path', 'page_id');
                            },
                        ])
                        ->where('status', true)
                        ->orderBy('priority', 'ASC');
                },
                'category_translation' => function ($query) {
                    $query->select('id', 'path');
                },
                'product_translation' => function ($query) {
                    $query->select('id', 'path');
                },
                'page_translation' => function ($query) {
                    $query->select('page_translations.id', 'path', 'is_home', 'page_id')
                        ->join('pages', 'pages.id', '=', 'page_translations.page_id');
                },
            ])
            ->when(empty($userLevelId), function ($q) {
                $q->where('is_private', false);
            })
            ->orderBy('priority', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model
            ->where('language_id', $data['language_id'])
            ->where('type', $data['type'])
            ->with([
                'category_translation' => function ($categoryTranslationQuery) {
                    $categoryTranslationQuery->select('id', 'category_id');
                },
                'product_translation' => function ($categoryTranslationQuery) {
                    $categoryTranslationQuery->select('id', 'product_id');
                },
                'page_translation' => function ($categoryTranslationQuery) {
                    $categoryTranslationQuery->select('id', 'page_id');
                },
            ])
            ->get();
    }
}
