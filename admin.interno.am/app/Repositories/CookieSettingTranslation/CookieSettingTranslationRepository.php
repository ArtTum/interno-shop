<?php

namespace App\Repositories\CookieSettingTranslation;

use App\Repositories\BaseRepository;
use App\Models\CookieSettingTranslation;

class CookieSettingTranslationRepository extends BaseRepository
{
    public function __construct(CookieSettingTranslation $model)
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
}
