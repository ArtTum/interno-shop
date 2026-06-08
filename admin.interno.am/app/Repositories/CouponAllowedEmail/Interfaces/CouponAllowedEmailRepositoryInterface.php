<?php

namespace App\Repositories\CouponAllowedEmail\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CouponAllowedEmailRepositoryInterface
{
    public function insert(array $data): bool;
}
