<?php

namespace App\Repositories\DocumentSettingTranslation;

use App\Models\DocumentSettingTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\DocumentSettingTranslation\Interfaces\DocumentSettingTranslationRepositoryInterface;

class DocumentSettingTranslationRepository extends BaseRepository implements DocumentSettingTranslationRepositoryInterface
{
    public function __construct(DocumentSettingTranslation $model)
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
        return $this->model->destroy($id);
    }
}
