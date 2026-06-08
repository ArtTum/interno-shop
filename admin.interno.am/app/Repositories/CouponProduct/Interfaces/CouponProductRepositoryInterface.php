<?php

namespace App\Repositories\CouponProduct\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CouponProductRepositoryInterface
{
    public function insert(array $data): bool;
}
