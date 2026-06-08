<?php

namespace App\Repositories\CouponCategory\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CouponCategoryRepositoryInterface
{
    public function insert(array $data): bool;
}
