<?php

namespace App\Repositories\OrderEmail;

use App\Repositories\BaseRepository;
use App\Models\OrderEmail;

class OrderEmailRepository extends BaseRepository
{
   public function __construct(OrderEmail $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function checkSendEmail(int $orderId, $status): bool
    {
        return $this->model->where('order_id', $orderId)->where('status', $status)->exists();
    }

    public function checkSendEmailByStatuses(int $orderId, array $statuses): bool
    {
        return $this->model->where('order_id', $orderId)->whereIn('status', $statuses)->exists();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
