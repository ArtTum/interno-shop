<?php

namespace App\Repositories\DpdDepot;

use App\Repositories\BaseRepository;
use App\Models\DpdDepot;
use Illuminate\Database\Eloquent\Model;

class DpdDepotRepository extends BaseRepository
{
    public function __construct(DpdDepot $model)
    {
        $this->model = $model;
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }
}
