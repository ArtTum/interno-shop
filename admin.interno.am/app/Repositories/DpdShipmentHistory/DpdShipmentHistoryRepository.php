<?php

namespace App\Repositories\DpdShipmentHistory;

use App\Repositories\BaseRepository;
use App\Models\DpdShipmentHistory;
use Illuminate\Database\Eloquent\Model;

class DpdShipmentHistoryRepository extends BaseRepository
{
    public function __construct(DpdShipmentHistory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }
}
