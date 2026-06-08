<?php

namespace App\Repositories\OrderRefund\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OrderRefundRepositoryInterface
{
    public function update(string $whereField, string|int $whereValue, array $data): bool;

    public function updateOrCreateByOrderIdWithLogic(int $orderId, array $params): Model;
}
