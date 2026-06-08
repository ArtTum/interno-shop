<?php

namespace App\Repositories\MediaTranslation;

use App\Models\MediaTranslation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MediaTranslationRepository extends BaseRepository
{
    public function __construct(MediaTranslation $model)
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

    public function fetchByFieldLanguage(string $whereField, string|int $whereValue, string $selectedFields, array $data): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->where('language_id', $data['language_id'])
            ->first();
    }

    public function getTranslationByMediaIdAndLanguageId(int $categoryId, int $languageId)
    {
        return $this->model->select(['alt', 'media_id'])
            ->where('media_id', $categoryId)
            ->where('language_id', $languageId)
            ->first();
    }
}
