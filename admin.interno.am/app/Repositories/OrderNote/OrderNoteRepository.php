<?php

namespace App\Repositories\OrderNote;

use App\Models\OrderNote;
use App\Repositories\BaseRepository;
use App\Repositories\OrderNote\Interfaces\OrderNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderNoteRepository extends BaseRepository implements OrderNoteRepositoryInterface
{
    public function __construct(OrderNote $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function customerNote(int $userId, int $orderId, $selectedFields): Model|null
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where('user_id', $userId)
            ->where('order_id', $orderId)
            ->first();
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
