<?php

namespace App\Repositories\PermalinkTranslation;

use App\Constants\PermalinkConstants;
use App\Repositories\BaseRepository;
use App\Models\PermalinkTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PermalinkTranslationRepository extends BaseRepository
{
    public function __construct(PermalinkTranslation $model)
    {
        $this->model = $model;
    }

    public function getTypeBySlug(string $slug, int $languageId)
    {
        return $this->model->select('type')
            ->where('slug', $slug)
            ->where('language_id', $languageId)
            ->value('type');
    }

    public function getSlugByType(int $type, int $languageId)
    {
        return $this->model->select('slug')
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->value('slug');
    }

    public function getSlugByTypes(array $type, int $languageId)
    {
        $permalinks = PermalinkConstants::PAGE_TYPES;

        return $this->model->select('slug', DB::raw("CASE type
                    WHEN {$permalinks['checkout']} THEN 'checkout'
                    WHEN {$permalinks['cart']} THEN 'cart'
                    END as type"))
            ->whereIn('type', $type)
            ->where('language_id', $languageId)
            ->pluck('slug', 'type')
            ->toArray();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->where('language_id', $params['language_id'])->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function updateByTypeAndLanguageId(int $type, int $languageId, array $data): bool
    {
        return $this->model
            ->where('type', $type)
            ->where('language_id', $languageId)->first()
            ->update($data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchTranslations(int $languageId)
    {
        return $this->model->select('type', 'slug')
            ->where('language_id', $languageId)
            ->get();
    }

    public function getAllLanguagesByType(string $type)
    {
        return $this->model->select('slug', 'language_id')
            ->where('type', $type)
            ->get()
            ->keyBy('language_id');
    }
}
