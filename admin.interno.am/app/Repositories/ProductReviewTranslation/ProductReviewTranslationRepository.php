<?php

namespace App\Repositories\ProductReviewTranslation;

use App\Repositories\BaseRepository;
use App\Models\ProductReviewTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ProductReviewTranslationRepository extends BaseRepository
{
    public function __construct(ProductReviewTranslation $model)
    {
        $this->model = $model;
    }

    public function updateOrInsert(array $data)
    {
        return $this->model->updateOrInsert(
            [
                'language_id' => $data['language_id'],
                'product_review_id' => $data['product_review_id'],
            ],
            $data
        );
    }

    public function getByLanguageText(int $languageId, int $productReviewId, string $vendorKey)
    {
        $val = Cache::tags([$vendorKey])->get("{$vendorKey}:{$productReviewId}_{$languageId}_review_translation");

        if ($val) return $val;

        $val = $this->model->select('text')
            ->where('language_id', $languageId)
            ->where('product_review_id', $productReviewId)
            ->value('text');

        Cache::tags([$vendorKey])->put("{$vendorKey}:{$productReviewId}_{$languageId}_review_translation", $val);

        return $val;
    }
}
