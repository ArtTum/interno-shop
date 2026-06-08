<?php

namespace App\Repositories\DpdShipment;

use App\Repositories\BaseRepository;
use App\Models\DpdShipment;
use Illuminate\Database\Eloquent\Model;

class DpdShipmentRepository extends BaseRepository
{
    public function __construct(DpdShipment $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function getIdByParcelNumber(string $parcelNumber): ?int
    {
        return $this->model->select('id')->where('parcel_number', $parcelNumber)->value('id');
    }

    public function getWithHistoryByParcelNumber(string $parcelNumber): ?Model
    {
        return $this->model
            ->with(['histories' => function ($query) {
                $query->orderBy('status_date', 'desc');
            }])
            ->where('parcel_number', $parcelNumber)
            ->first();
    }
}
