<?php

namespace App\Repositories\OrderSentGiftCard;

use App\Repositories\BaseRepository;
use App\Models\OrderSentGiftCard;

class OrderSentGiftCardRepository extends BaseRepository
{
    public function __construct(OrderSentGiftCard $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function checkSentStatus(int $orderId): bool
    {
        return $this->model->where('order_id', $orderId)->exists();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
