<?php

namespace App\Repositories\CartItem;

use App\Models\CartItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class CartItemRepository extends BaseRepository
{
    public function __construct(CartItem $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function updateOrCreateByKey(int $cartId, array $item): int
    {
        $cartItem = $this->model->select('id')
            ->where('cart_id', $cartId)
            ->where('searching_key', $item['searching_key'])
            ->first();

        if (!$cartItem) {
            $cartItem = $this->model->create([
                'cart_id' => $cartId,
                'product_variant_id' => $item['product_variant_id'],
                'gift_card_details' => !empty($item['gift_card_details']) ? $item['gift_card_details'] : null,
                'searching_key' => $item['searching_key'],
                'quantity' => $item['quantity'],
            ]);
        } else {
            $cartItem->update([
                'quantity' => $item['quantity'],
            ]);
        }

        return $cartItem->id;
    }

    public function bulkDeleteParentsByKeys(int $cartId, array $searchingKeys)
    {
        return $this->model->whereNull('parent_id')
            ->where('cart_id', $cartId)
            ->whereNotIn('searching_key', $searchingKeys)
            ->delete();
    }

    public function bulkDeleteExtrasByIds(int $parentId, array $ids)
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->whereNotIn('id', $ids)
            ->delete();
    }

    public function createOrUpdateExtras(array $data)
    {
        $extraItem = $this->model->select('id')
            ->where('parent_id', $data['parent_id'])
            ->where('product_variant_id', $data['product_variant_id'])
            ->first();


        if (!$extraItem) {
            $extraItem = $this->model->create($data);
        } else {
            $extraItem->update([
                'quantity' => $data['quantity'],
            ]);
        }

        return $extraItem->id;
    }
}
