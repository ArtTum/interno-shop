<?php

namespace App\Repositories\CouponCategory;

use App\Models\CouponCategory;
use App\Repositories\BaseRepository;
use App\Repositories\CouponCategory\Interfaces\CouponCategoryRepositoryInterface;

class CouponCategoryRepository extends BaseRepository implements CouponCategoryRepositoryInterface
{
    public function __construct(CouponCategory $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchIdsByCouponId(int $couponId, bool $excluded): array
    {
        return $this->model->select('category_id')
            ->where('coupon_id', $couponId)
            ->where('is_excluded', $excluded)
            ->pluck('category_id')
            ->toArray();
    }

    public function deleteByCouponAndCategoryIds(int $couponId, array $categoryIds, bool $excluded): bool
    {
        return $this->model->where('coupon_id', $couponId)
            ->whereIn('category_id', $categoryIds)
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
