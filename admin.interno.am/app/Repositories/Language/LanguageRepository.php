<?php

namespace App\Repositories\Language;

use App\Models\Language;
use App\Repositories\BaseRepository;
use App\Repositories\Language\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when((isset($params['status']) && $params['status'] >= 0), function ($statusQuery) use ($params) {
                $statusQuery->where('status', $params['status']);
            })
            ->when((isset($params['draft']) && $params['draft'] >= 0), function ($statusQuery) use ($params) {
                $statusQuery->where('draft', $params['draft']);
            });
    }

    public function fetchForAdmin(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins, ?array $relatedParams = []): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->leftJoin('currencies', function ($join) {
                $join->on('currencies.id', '=', 'languages.currency_id');
            })
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->when(!empty($relatedParams), function ($query) use ($relatedParams) {
                $query->with([
                    $relatedParams['relation_name'] => function ($query) use ($relatedParams) {
                        $query->select(DB::raw($relatedParams['select']))->where($relatedParams['where_field'], $relatedParams['id']);
                    }
                ]);
            })->get();
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins, ?array $relatedParams = []): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->when(!empty($relatedParams), function ($query) use ($relatedParams) {
                $query->with([
                    $relatedParams['relation_name'] => function ($query) use ($relatedParams) {
                        $query->select(DB::raw($relatedParams['select']))->where($relatedParams['where_field'], $relatedParams['id']);
                    }
                ]);
            })->get();
    }

    public function getEmailList(string $select): Collection
    {
        return $this->model->select(DB::raw($select))
            ->whereNotNull('email')
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

    public function updateBaseForAllFalse(): bool
    {
        return $this->model->where('id', '>=', 0)->update(['base' => false]);
    }

    public function updateDefaultHreflangForAllFalse(): bool
    {
        return $this->model->where('id', '>=', 0)->update(['default_hreflang' => false]);
    }

    public function getBaseId(): int
    {
        return $this->model->select('id')->orderByRaw("FIELD(base, true) DESC, name ASC")->value('id');
    }

    public function getBaseCode(): string
    {
        return $this->model->select('code')->orderByRaw("FIELD(base, true) DESC, name ASC")->value('code');
    }

    public function getCodeById(int $id): ?string
    {
        return $this->model->select('code')->where('id', $id)->value('code');
    }

    public function getIdByCode(string $code): ?string
    {
        return $this->model->select('id')->where('code', $code)->value('id');
    }

    public function getLocales()
    {
        return $this->model->select('code')
            ->where('status', true)
            ->where('draft', false)
            ->orderByRaw("FIELD(base, true) DESC, code ASC")
            ->pluck('code')
            ->toArray();
    }

    public function getLanguages()
    {
        return $this->model->select('name', 'code')
            ->where('status', true)
            ->where('draft', false)
            ->pluck('name', 'code');
    }

    public function getLanguagesForExport()
    {
        return $this->model->select('id', 'name', 'code')
            ->where('status', true)
            ->where('draft', false)
            ->get();
    }

    public function getLanguageId(string $locale)
    {
        return $this->model->select('id as language_id', 'currency_id')
            ->where('languages.code', $locale)
            ->where('languages.status', true)
            ->where('languages.draft', false)
            ->first();
    }

    public function getLanguagesWithoutNeededProductTranslation(int $neededId, string $relationName, string $fieldName, array $languageIds = [])
    {
        return $this->model->select('id', 'code', 'hreflang')
            ->whereDoesntHave($relationName, function ($q) use ($neededId, $fieldName) {
                $q->where($fieldName, $neededId);
            })
            ->when(!empty($languageIds), function ($q) use ($languageIds) {
                $q->whereIn('id', $languageIds);
            })
            ->get();
    }

    public function getLanguagesCodes()
    {
        return $this->model->select('code', 'id')
            ->where('status', true)
            ->pluck('code', 'id');
    }
}
