<?php

namespace App\Repositories\OrderRefundHistory;

use App\Repositories\BaseRepository;
use App\Models\OrderRefundHistory;
use Illuminate\Database\Eloquent\Model;

class OrderRefundHistoryRepository extends BaseRepository
{
    public function __construct(OrderRefundHistory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
}
