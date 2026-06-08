<?php

namespace App\Repositories\GlobalDocumentSettingTranslation;

use App\Models\GlobalDocumentSettingTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\GlobalDocumentSettingTranslation\Interfaces\GlobalDocumentSettingTranslationRepositoryInterface;

class GlobalDocumentSettingTranslationRepository extends BaseRepository implements GlobalDocumentSettingTranslationRepositoryInterface
{
    public function __construct(GlobalDocumentSettingTranslation $model)
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
