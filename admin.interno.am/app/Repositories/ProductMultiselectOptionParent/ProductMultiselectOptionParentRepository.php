<?php

namespace App\Repositories\ProductMultiselectOptionParent;

use App\Repositories\BaseRepository;
use App\Models\ProductMultiselectOptionParent;

class ProductMultiselectOptionParentRepository extends BaseRepository
{
   public function __construct(ProductMultiselectOptionParent $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function updateOrCreateByItemId(int $qty, int $multiselectOptionId, int $itemId): bool
    {
        $now = now()->toDateTimeString();
        $productVariantParent = $this->model->select('id')
            ->where('item_id', $itemId)
            ->where('product_multiselect_option_id', $multiselectOptionId)
            ->first();

        if ($productVariantParent) {
            $productVariantParent->update([
                'quantity' => $qty
            ]);
        } else {
            $this->model->insert(merge_dates_for_insert([
                'product_multiselect_option_id' => $multiselectOptionId,
                'quantity' => $qty,
                'item_id' => $itemId,
            ], $now));
        }

        return true;
    }

    public function deleteByOptionId(int $optionId): bool
    {
        return $this->model->where('product_multiselect_option_id', $optionId)->delete();
    }
}
