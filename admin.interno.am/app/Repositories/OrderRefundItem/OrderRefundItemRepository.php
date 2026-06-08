<?php

namespace App\Repositories\OrderRefundItem;

use App\Models\OrderRefundItem;
use App\Repositories\BaseRepository;
use App\Repositories\OrderRefundItem\Interfaces\OrderRefundItemRepositoryInterface;

class OrderRefundItemRepository extends BaseRepository implements OrderRefundItemRepositoryInterface
{
    public function __construct(OrderRefundItem $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function bulkDeleteByIds(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
