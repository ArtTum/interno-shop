<?php

namespace App\Repositories\Calculator;

use App\Repositories\BaseRepository;
use App\Models\Calculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CalculatorRepository extends BaseRepository
{
    public function __construct(Calculator $model)
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
            ->when($params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('calculator_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('calculator_translation', function ($q) use ($params) {
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
                'calculator_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'calculator_id', 'config', 'name')->where('language_id', $baseLanguageId);
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'calculator_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'calculator_id', 'config', 'name')->where('language_id', $languageId);
                    }
                ]);
            })
            ->first();
    }

    public function fetchAsParam(int $languageId): Collection
    {
        return $this->model->select('calculators.id as value', 'name as label')
            ->join('calculator_translations', 'calculators.id', '=', 'calculator_translations.calculator_id')
            ->where('language_id', $languageId)
            ->get();
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}
