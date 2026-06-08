<?php

namespace App\Repositories\GeneralSettingTranslation;

use App\Models\GeneralSettingTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\GeneralSettingTranslation\Interfaces\GeneralSettingTranslationRepositoryInterface;

class GeneralSettingTranslationRepository extends BaseRepository implements GeneralSettingTranslationRepositoryInterface
{
    public function __construct(GeneralSettingTranslation $model)
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
