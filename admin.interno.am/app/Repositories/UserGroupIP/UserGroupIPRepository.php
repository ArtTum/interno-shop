<?php

namespace App\Repositories\UserGroupIP;

use App\Repositories\BaseRepository;
use App\Models\UserGroupIP;

class UserGroupIPRepository extends BaseRepository
{
    public function __construct(UserGroupIP $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function bulkDelete(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function deleteOldIPs()
    {
        return $this->model->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
