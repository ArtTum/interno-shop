<?php

namespace App\Repositories\OrderCustomerEmail;

use App\Repositories\BaseRepository;
use App\Models\OrderCustomerEmail;
use Illuminate\Database\Eloquent\Model;

class OrderCustomerEmailRepository extends BaseRepository
{
   public function __construct(OrderCustomerEmail $model)
    {
        $this->model = $model;
    }

    public function fetchEmails() {
        return $this->model->select('order_customer_emails.id', 'order_id', 'name', 'last_name', 'message')
            ->leftJoin('users', 'users.id', '=', 'order_customer_emails.user_id')
            ->orderBy('order_customer_emails.created_at', 'DESC')
            ->get();
    }


    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }
}
