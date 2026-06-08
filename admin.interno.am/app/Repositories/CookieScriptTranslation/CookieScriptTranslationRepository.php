<?php

namespace App\Repositories\CookieScriptTranslation;

use App\Repositories\BaseRepository;
use App\Models\CookieScriptTranslation;

class CookieScriptTranslationRepository extends BaseRepository
{
    public function __construct(CookieScriptTranslation $model)
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
}
