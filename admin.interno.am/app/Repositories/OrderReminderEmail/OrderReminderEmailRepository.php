<?php

namespace App\Repositories\OrderReminderEmail;

use App\Repositories\BaseRepository;
use App\Models\OrderReminderEmail;

class OrderReminderEmailRepository extends BaseRepository
{
    public function __construct(OrderReminderEmail $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function checkSendEmail(int $orderId, $reminderEmailId): bool
    {
        return $this->model->where('order_id', $orderId)->where('reminder_email_id', $reminderEmailId)->exists();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
