<?php

namespace App\Repositories\Variable;

use App\Constants\TranslationConstants;
use App\Models\Variable;
use App\Repositories\BaseRepository;
use App\Repositories\Variable\Interfaces\VariableRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VariableRepository extends BaseRepository implements VariableRepositoryInterface
{
    public function __construct(Variable $model)
    {
        $this->model = $model;
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['for_front'] >= 0, function ($query) use ($params) {
                $query->where('for_front', $params['for_front']);
            })
            ->when($params['is_invalid'] == 1, function ($query) use ($params) {
                $query->whereDoesntHave('selected_variable_translation', function ($query) use ($params) {
                    $query->where('language_id', $params['language_id']);
                });
            })->when($params['is_invalid'] == 0, function ($query) use ($params) {
                $query->whereHas('selected_variable_translation', function ($query) use ($params) {
                    $query->where('language_id', $params['language_id']);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins);

        if (!empty($pagination)) {
            $query->limit($pagination['limit'])
                ->offset($pagination['offset']);
        }

        return $query->with([
            'variable_translation' => function ($translationQuery) use ($params) {
                $baseLanguageId = $params['base_language_id'];
                $translationQuery->select('id', 'variable_id', 'value')
                    ->where('language_id', $baseLanguageId);
            },
            'selected_variable_translation' => function ($translationQuery) use ($params) {
                $languageId = $params['language_id'];
                $translationQuery->select('id', 'variable_id', 'value')
                    ->where('language_id', $languageId);
            },
        ])->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchForVariablesTranslations(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'variables.id', 'id');

        return $fullData;
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'variable_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'variable_id', 'value')->where('language_id', $languageId);
                }
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function fetchAll(array $params): array
    {
        $languageId = $params['language_id'];;

        return $this->model
            ->select('key', 'value')
            ->join('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where('variable_translations.language_id', $languageId)
            ->whereIn('key', TranslationConstants::DOCUMENT_PDF)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function fetchForExport(array $params): Collection
    {
        return $this->model->with([
            'variable_translation' => function ($translationQuery) use ($params) {
                $baseLanguageId = $params['base_language_id'];
                $translationQuery->select('variable_id', 'value')->where('language_id', $baseLanguageId);
            },
            'selected_variable_translation' => function ($translationQuery) use ($params) {
                $languageId = $params['language_id'];
                $translationQuery->select('variable_id', 'value')->where('language_id', $languageId);
            },
        ])->when(!filter_var($params['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($params) {
            $query->whereIn('id', $params['ids'])
                ->orderBy($params['ordering_field'], $params['ordering_direction']);;
        })->get();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchTranslationsByLanguageForFront(int $languageId)
    {
        return $this->model->select('key', 'value')
            ->where('for_front', true)
            ->join('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where('language_id', $languageId)
            ->pluck('value', 'key');
    }

    public function getByKey(int $languageId, string $key): ?string
    {
        return $this->model->select('value')
            ->where('key', $key)
            ->join('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where('language_id', $languageId)
            ->value('value');
    }

    public function getByKeys(int $languageId, array $key)
    {
        return $this->model->select('value', 'key')
            ->whereIn('key', $key)
            ->join('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where('language_id', $languageId)
            ->pluck('value', 'key');
    }

    public function fetchForOffer(int $languageId)
    {
        return $this->model->select('key', 'value')
            ->where('key', 'like', '%offer%')
            ->leftJoin('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhereNull('language_id');
            })
            ->get()
            ->map(function ($item) {
                // If value is null, use key as value
                return [
                    'key' => $item->key,
                    'value' => $item->value ?? '{' . $item->key . '}',
                ];
            })
            ->pluck('value', 'key')
            ->toArray();
    }

    public function fetchDhlStatuses(int $languageId)
    {
        return $this->model->select('key', 'value')
            ->where('key', 'like', '%dhl_%')
            ->leftJoin('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhereNull('language_id');
            })
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    public function fetchDpdStatuses(int $languageId)
    {
        return $this->model->select('key', 'value')
            ->where('key', 'like', '%dpd_%')
            ->leftJoin('variable_translations', 'variables.id', '=', 'variable_translations.variable_id')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhereNull('language_id');
            })
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
}
