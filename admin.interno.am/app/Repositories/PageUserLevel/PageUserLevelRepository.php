<?php

namespace App\Repositories\PageUserLevel;

use App\Repositories\BaseRepository;
use App\Models\PageUserLevel;

class PageUserLevelRepository extends BaseRepository
{
   public function __construct(PageUserLevel $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }


    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }


    public function fetchIdsByPageId(int $pageId): array
    {
        return $this->model->select('user_level_id')->where('page_id', $pageId)->pluck('user_level_id', 'user_level_id')->toArray();
    }

    public function deleteByPageAndUserLevelIds(int $pageId, array $userLevelIds): bool
    {
        return $this->model->where('page_id', $pageId)->whereIn('user_level_id', $userLevelIds)->delete();
    }
}
