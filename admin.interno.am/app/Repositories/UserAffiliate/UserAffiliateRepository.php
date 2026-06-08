<?php

namespace App\Repositories\UserAffiliate;

use App\Repositories\BaseRepository;
use App\Models\UserAffiliate;
use Illuminate\Database\Eloquent\Model;

class UserAffiliateRepository extends BaseRepository
{
    public function __construct(UserAffiliate $model)
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
}
