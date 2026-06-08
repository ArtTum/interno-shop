<?php

namespace App\Repositories\EmailSettingTranslation;

use App\Models\EmailSettingTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\EmailSettingTranslation\Interfaces\EmailSettingTranslationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class EmailSettingTranslationRepository extends BaseRepository implements EmailSettingTranslationRepositoryInterface
{
    public function __construct(EmailSettingTranslation $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function getTranslationByEmailSettingIdAndLanguageId(int $attributeId, int $languageId)
    {
        return $this->model->select('*')
            ->where('email_setting_id', $attributeId)
            ->where('language_id', $languageId)
            ->first();
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
