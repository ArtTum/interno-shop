<?php

namespace App\Repositories\CouponProduct;

use App\Models\CouponProduct;
use App\Repositories\BaseRepository;
use App\Repositories\CouponProduct\Interfaces\CouponProductRepositoryInterface;

class CouponProductRepository extends BaseRepository implements CouponProductRepositoryInterface
{
    public function __construct(CouponProduct $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchIdsByCouponId(int $couponId, bool $excluded): array
    {
        return $this->model->select('product_id')
            ->where('coupon_id', $couponId)
            ->where('is_excluded', $excluded)
            ->pluck('product_id')
            ->toArray();
    }

    public function deleteByCouponAndProductIds(int $couponId, array $productIds, bool $excluded): bool
    {
        return $this->model->where('coupon_id', $couponId)
            ->whereIn('product_id', $productIds)
            ->where('is_excluded', $excluded)
            ->delete();
    }

    public function deleteByCouponId(int $couponId, bool $excluded): bool
    {
        return $this->model->where('coupon_id', $couponId)
            ->where('is_excluded', $excluded)
            ->delete();
    }
}
