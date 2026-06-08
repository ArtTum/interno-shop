<?php

namespace App\Repositories\LeadProjectTranslation;

use App\Repositories\BaseRepository;
use App\Models\LeadProjectTranslation;
use Illuminate\Database\Eloquent\Model;
class LeadProjectTranslationRepository extends BaseRepository
{
    public function __construct(LeadProjectTranslation $model)
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

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchByProjectAndLanguageId(int $projectId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select($selectFields)
            ->where('project_id', $projectId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function getProjectsNamesByLanguage(string $languageId)
    {
        return $this->model->select('name', 'project_id')
            ->where('language_id', $languageId)
            ->pluck('name', 'project_id');
    }
}
