<?php

namespace App\Repositories\PageCustomerGroup;

use App\Repositories\BaseRepository;
use App\Models\PageCustomerGroup;

class PageCustomerGroupRepository extends BaseRepository
{
   public function __construct(PageCustomerGroup $model)
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
        return $this->model->select('customer_group_id')->where('page_id', $pageId)->pluck('customer_group_id')->toArray();
    }

    public function deleteByPageAndCustomerGroupIds(int $pageId, array $customerGroupIds): bool
    {
        return $this->model->where('page_id', $pageId)->whereIn('customer_group_id', $customerGroupIds)->delete();
    }
}
