<?php

namespace App\Repositories\OrderDocument;

use App\Constants\OrderConstants;
use App\Models\OrderDocument;
use App\Repositories\BaseRepository;
use App\Repositories\OrderDocument\Interfaces\OrderDocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderDocumentRepository extends BaseRepository implements OrderDocumentRepositoryInterface
{
    public function __construct(OrderDocument $model)
    {
        $this->model = $model;
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByFields(array $data, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where('order_id', $data['order_id'])
            ->where('document_setting_id', $data['document_setting_id'])
            ->first();
    }

    public function fetchById(string $whereField, string|int $whereValue, string $selectedFields): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function createOrUpdate(array $data): bool
    {
        if ($data['id']) {
            return parent::update('id', $data['id'], $data);
        } else {
            return parent::insert($data);
        }
    }

    public function lastId(array $data): int
    {
        $documentNumber = $this->model->select('document_number')
            ->where('document_setting_id', $data['document_setting_id'])
            ->max('document_number');

        $documentNumber = $documentNumber ?? 0;

        if (isset($data['id']) && $data['id']) {
            return $documentNumber;
        } else {
            return $documentNumber + 1;
        }
    }
    public function originalInvoiceNumber(int $orderId): string|int
    {
        $result = $this->model->select('generate_number')
            ->where('order_id', $orderId)
            ->where('document_setting_id', OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE)
            ->value('generate_number');

        if ($result === null) {
           return '';
        }

        return $result;
    }

    public function getInvoice(int $orderId): array|false
    {
        $result = $this->model->select('generate_number', 'updated_at')
            ->where('order_id', $orderId)
            ->where('document_setting_id', OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE)
            ->first();

        if ($result === null) {
            return false;
        }

        return $result->toArray();
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
