<?php

namespace App\Repositories\ShippingLabel;

use App\Constants\OrderConstants;
use App\Constants\ShippingConstants;
use App\Models\ShippingLabel;
use App\Repositories\BaseRepository;
use App\Repositories\ShippingLabel\Interfaces\ShippingLabelRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShippingLabelRepository extends BaseRepository implements ShippingLabelRepositoryInterface
{
    public function __construct(ShippingLabel $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return self::fetchQuery($selectedFields, [], [], [], [], [])
            ->where($whereField, $whereValue)
            ->where('label_deleted', 0)
            ->first();
    }

    public function fetchInvoiceEmail(string $whereField, string|int $whereValue, string $selectedFields, array $whereParams = []): Collection
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where('type', ShippingConstants::TNT_TYPE)
            ->when(!empty($whereParams), function ($query) use ($whereParams) {
                foreach ($whereParams as $key => $value) {
                    $query->where($key, $value);
                }
            })
            ->where($whereField, $whereValue)
            ->with(['order' => function ($query) {
                return $query->select('id', 'is_dangerous', 'status')
                    ->with(['order_document' => function ($query) {
                        return $query->select('id', 'order_id', 'path', 'base_path', 'document_setting_id')
                            ->where('document_setting_id', OrderConstants::ORDER_DOCUMENT_TYPE_PROFORMA);
                    }]);
            }])
            ->get();
    }

    public function checkShippingLabel(int $orderId): bool
    {
        return $this->model
            ->where('order_id', $orderId)
            ->where('label_deleted', 0)
            ->exists();
    }

    public function shippingLabelMailCount(): int
    {
        return $this->model
            ->where('type', ShippingConstants::TNT_TYPE)
            ->where('label_deleted', 0)
            ->where('email_sent', 0)
            ->whereHas('order', function ($query) {
                $query->where('status', OrderConstants::STATUS_COMPLETED);
            })
            ->count();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function updateAll(string $whereField, array $whereValue, array $data): int
    {
        return $this->model->whereIn($whereField, $whereValue)->update($data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function deleteByField(string $whereField, string|int $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->delete();
    }
}
