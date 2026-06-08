<?php

namespace App\Repositories\CalculatorTranslation;

use App\Repositories\BaseRepository;
use App\Models\CalculatorTranslation;
use Illuminate\Database\Eloquent\Collection;

class CalculatorTranslationRepository extends BaseRepository
{
    public function __construct(CalculatorTranslation $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function fetchAsParam(int $languageId): Collection
    {
        return $this->model->select('id as value', 'name as label')
            ->where('language_id', $languageId)
            ->get();
    }

    public function getIdByCalculatorIdAndLanguageId(int $calculatorId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('calculator_id', $calculatorId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getCalculatorIdById(int $id)
    {
        return $this->model->select('calculator_id')
            ->where('id', $id)
            ->value('calculator_id');
    }

    public function getNeededTranslationIdByParams(int $translationId, int $languageId)
    {
        return $this->model->select('id')
            ->whereHas('calculator', function ($q) use ($translationId) {
                $q->whereHas('calculator_translation', function ($q) use ($translationId) {
                    $q->where('id', $translationId);
                });
            })
            ->where('language_id', $languageId)
            ->value('id');
    }
}
