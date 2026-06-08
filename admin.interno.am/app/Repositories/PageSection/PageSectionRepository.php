<?php

namespace App\Repositories\PageSection;

use App\Models\PageSection;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class PageSectionRepository extends BaseRepository
{
    public function __construct(PageSection $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByField(string $field, string $value): bool
    {
        return $this->model->where($field, $value)->delete();
    }
}
