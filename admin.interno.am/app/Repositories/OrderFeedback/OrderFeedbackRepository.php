<?php

namespace App\Repositories\OrderFeedback;

use App\Repositories\BaseRepository;
use App\Models\OrderFeedback;

class OrderFeedbackRepository extends BaseRepository
{
    public function __construct(OrderFeedback $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchForWarehouse() {
        return $this->model->select('order_feedbacks.id as logID', 'order_id', 'user_id as shopuser',
            'type', 'message', 'users.gtin as employee_gtin')
            ->leftJoin('users', 'users.id', '=', 'order_feedbacks.employee_id')
            ->where('sent_warehouse', false)
            ->limit(150)
            ->orderBy('order_feedbacks.created_at', 'asc')
            ->get();
    }

    public function bulkUpdateSentStatus(array $ids)
    {
        return $this->model->whereIn('id', $ids)
            ->update(['sent_warehouse' => true]);
    }
}
