<?php

namespace App\Repositories\UserLevel;

use App\Repositories\BaseRepository;
use App\Models\UserLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLevelRepository extends BaseRepository
{
   public function __construct(UserLevel $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'user_level_translations' => function ($translationQuery) use ($languageId) {
                    $translationQuery
                        ->select('id', 'user_level_id', 'name', 'description')
                        ->where('language_id', $languageId);
                },
                'options' => function ($translationQuery) use ($languageId) {
                    $translationQuery
                        ->select('*');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->first();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('user_level_translations', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('user_level_translations', function ($q) use ($params) {
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
            })
            ->with([
                'user_level_translations' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'user_level_id', 'name')->where('language_id', $baseLanguageId);
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'original_path AS path'
                    ]);
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function getCurrentTierInfo(float $spent, int $languageId, string $select)
    {
        return $this->model
            ->select(DB::raw($select))
            ->where('spent', '<=', $spent)
            ->with([
                'user_level_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery
                        ->select('id', 'user_level_id', 'name', 'description')
                        ->where('language_id', $languageId);
                },
                'options' => function ($optionsQuery) use ($languageId) {
                    $optionsQuery
                        ->select('*');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->orderByDesc('spent')
            ->first();
    }

    public function getUserLevelId(float $spent)
    {
        return $this->model
            ->select('id')
            ->where('spent', '<=', $spent)
            ->orderByDesc('spent')
            ->value('id');
    }

    public function getUserLevelDiscount(int $id)
    {
        return $this->model
            ->select('discount')
            ->where('id', $id)
            ->value('discount');
    }

    public function getUserLevelCashback(int $id)
    {
        return $this->model
            ->select('cashback')
            ->where('id', $id)
            ->value('cashback');
    }

    public function getAllLevel(int $languageId, string $select)
    {
        return $this->model
            ->select(DB::raw($select))
            ->with([
                'user_level_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery
                        ->select('id', 'user_level_id', 'name', 'description')
                        ->where('language_id', $languageId);
                },
                'options' => function ($optionsQuery) use ($languageId) {
                    $optionsQuery
                        ->select('*');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->orderBy('spent')
            ->get();
    }
}
