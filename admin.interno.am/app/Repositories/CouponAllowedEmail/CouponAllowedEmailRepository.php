<?php

namespace App\Repositories\CouponAllowedEmail;

use App\Models\CouponAllowedEmail;
use App\Repositories\BaseRepository;
use App\Repositories\CouponAllowedEmail\Interfaces\CouponAllowedEmailRepositoryInterface;

class CouponAllowedEmailRepository extends BaseRepository implements CouponAllowedEmailRepositoryInterface
{
    public function __construct(CouponAllowedEmail $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchIdsByCouponId(int $couponId, ?bool $excluded): array
    {
        return $this->model->select('email')
            ->where('coupon_id', $couponId)
            ->pluck('email')
            ->toArray();
    }

    public function deleteByCouponAndEmails(int $couponId, array $emails, ?bool $excluded): bool
    {
        return $this->model->where('coupon_id', $couponId)
            ->whereIn('email', $emails)
            ->delete();
    }

    public function deleteByCouponId(int $couponId): bool
    {
        return $this->model->where('coupon_id', $couponId)
            ->delete();
    }
}
