<?php

namespace App\Repositories\VariableTranslation;

use App\Models\VariableTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\VariableTranslation\Interfaces\VariableTranslationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VariableTranslationRepository extends BaseRepository implements VariableTranslationRepositoryInterface
{
    public function __construct(VariableTranslation $model)
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

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByVariableAndLanguageId(int $variableId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('variable_id', $variableId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getTranslationByVariableIdAndLanguageId(int $categoryId, int $languageId)
    {
        return $this->model->select('*')
            ->where('variable_id', $categoryId)
            ->where('language_id', $languageId)
            ->first();
    }
}
