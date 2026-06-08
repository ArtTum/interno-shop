<?php

namespace App\Repositories\LeadSettingTranslation;

use App\Repositories\BaseRepository;
use App\Models\LeadSettingTranslation;

class LeadSettingTranslationRepository extends BaseRepository
{
    public function __construct(LeadSettingTranslation $model)
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
