<?php

namespace App\Repositories\ProductVariantParent;

use App\Models\ProductVariantParent;
use App\Repositories\BaseRepository;
use App\Repositories\ProductVariantParent\Interfaces\ProductVariantParentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProductVariantParentRepository extends BaseRepository implements ProductVariantParentRepositoryInterface
{
    public function __construct(ProductVariantParent $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function bulkDeleteBySKU(array $sku, int $productVariantId): bool
    {
        return $this->model
            ->join('items', 'items.id', '=', 'product_variant_parents.item_id')
            ->whereIn('items.sku', $sku)
            ->where('product_variant_id', $productVariantId)
            ->delete();
    }

    public function fetchSKUsByVariantId(int $variantId): array
    {
        return $this->model
            ->select('items.sku')
            ->join('items', 'items.id', '=', 'product_variant_parents.item_id')
            ->where('product_variant_id', $variantId)
            ->pluck('sku')
            ->toArray();
    }

    public function updateOrCreateByItemId(int $qty, int $productVariantId, int $itemId): bool
    {
        $now = now()->toDateTimeString();
        $productVariantParent = $this->model->select('id')
            ->where('item_id', $itemId)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if ($productVariantParent) {
            $productVariantParent->update([
                'quantity' => $qty
            ]);
        } else {
            $this->model->insert(merge_dates_for_insert([
                'product_variant_id' => $productVariantId,
                'quantity' => $qty,
                'item_id' => $itemId,
            ], $now));
        }

        return true;
    }

    public function deleteByVariantId(int $variantId): bool
    {
        return $this->model->where('product_variant_id', $variantId)->delete();
    }
}
