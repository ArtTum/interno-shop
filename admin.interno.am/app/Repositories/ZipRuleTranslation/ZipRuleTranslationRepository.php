<?php

namespace App\Repositories\ZipRuleTranslation;

use App\Models\ZipRuleTranslation;
use App\Repositories\BaseRepository;
use App\Services\General\CustomSlugService;
use Illuminate\Database\Eloquent\Model;

class ZipRuleTranslationRepository extends BaseRepository
{
   public function __construct(ZipRuleTranslation $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        $data = CustomSlugService::setPathBySlugCategory($data, 'catalog');
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        $data = CustomSlugService::setPathBySlugCategory($data, 'catalog');

        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }
}
