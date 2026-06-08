<?php

namespace App\Repositories\OrderSubDocument;

use App\Repositories\BaseRepository;
use App\Models\OrderSubDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderSubDocumentRepository extends BaseRepository
{
    public function __construct(OrderSubDocument $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchById(string $whereField, string|int $whereValue, string $selectedFields): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function documentNumber(int $orderDocumentId): int
    {
        $documentNumber = $this->model->select('document_number')
            ->where('order_document_id', $orderDocumentId)
            ->count('document_number');

        return $documentNumber + 1;
    }
}
