<?php

namespace App\Repositories\PostCategory;
use App\Models\PostCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostCategoryRepository extends BaseRepository
{
    public function __construct(PostCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('post_category_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('post_category_translation', function ($q) use ($params) {
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
                'post_category_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'post_category_id', 'name', 'description')->where('language_id', $baseLanguageId);
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
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

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'post_category_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'post_category_id', 'name', 'description', 'slug')->where('language_id', $languageId);
                    }
                ]);
            })
            ->first();
    }
}
