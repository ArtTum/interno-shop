<?php

namespace App\Repositories\PageSectionComponent;

use App\Models\PageSectionComponent;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class PageSectionComponentRepository extends BaseRepository
{
    public function __construct(PageSectionComponent $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }
}
