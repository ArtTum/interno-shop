<?php

namespace App\Repositories\SocialTranslation;

use App\Models\SocialTranslation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class SocialTranslationRepository extends BaseRepository
{
    public function __construct(SocialTranslation $model)
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

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }
}
