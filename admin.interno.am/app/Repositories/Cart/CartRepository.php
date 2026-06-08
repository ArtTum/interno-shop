<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartRepository extends BaseRepository
{
    public function __construct(Cart $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchCartItemsQuery($query, int $languageId, float $rate)
    {
        return $query->select('id', 'cart_id', 'product_variant_id', 'quantity', 'searching_key', 'gift_card_details')
            ->whereNull('parent_id')
            ->with([
                'product_variant' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'stock_status')
                        ->where('status', true)
                        ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                            $query->with([
                                'product_variant_prices' => function ($query) use ($rate) {
                                    $query->select([
                                        'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                                    ])
                                        ->where('customer_group_id', Auth::user()->customer_group_id)
                                        ->orderBy('min', 'desc');
                                },
                            ]);
                        })
                        ->with([
                            'media' => function ($imageQuery) {
                                $imageQuery->select([
                                    'id', 'path as general_path'
                                ]);
                            },
                            'product' => function ($query) use ($languageId) {
                                $query->select('products.id', 'type', 'name', 'sub_name', 'path', 'overwrite_price')
                                    ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                                    ->where('product_translations.language_id', $languageId)
                                    ->with([
                                        'product_variant_main' => function ($attributesQuery) {
                                            $attributesQuery->select('product_id', 'sku', 'media_id', 'stock_status')
                                                ->with([
                                                    'media' => function ($imageQuery) {
                                                        $imageQuery->select([
                                                            'id', 'path as general_path'
                                                        ]);
                                                    }
                                                ]);
                                        },
                                    ]);
                            },
                            'attributes' => function ($query) use ($languageId) {
                                $query->select('attributes.id', 'slug', 'attribute_type_id', 'value')
                                    ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                                    ->where('attribute_translations.language_id', $languageId)
                                    ->with([
                                        'attribute_type' => function ($query) use ($languageId) {
                                            $query->select('attribute_types.id', 'slug', 'name')
                                                ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                                ->where('attribute_type_translations.language_id', $languageId);
                                        }
                                    ]);
                            }
                        ]);
                },
                'cart_extra_products' => function ($query) use ($languageId, $rate) {
                    $query->select('parent_id', 'product_variant_id')
                        ->with([
                            'product_variant' => function ($query) use ($languageId, $rate) {
                                $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                                    DB::raw("sales_price * {$rate} as sales_price"), 'stock_status')
                                    ->where('status', true)
                                    ->with([
                                        'product' => function ($query) use ($languageId) {
                                            $query->select('products.id', 'name')
                                                ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                                                ->where('product_translations.language_id', $languageId)
                                                ->with([
                                                    'product_variant_main' => function ($attributesQuery) {
                                                        $attributesQuery->select('product_id', 'stock_status');
                                                    },
                                                ]);
                                        },
                                        'attributes' => function ($query) use ($languageId) {
                                            $query->select('attributes.id', 'attribute_type_id', 'value')
                                                ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                                                ->where('attribute_translations.language_id', $languageId)
                                                ->with([
                                                    'attribute_type' => function ($query) use ($languageId) {
                                                        $query->select('attribute_types.id', 'name')
                                                            ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                                            ->where('attribute_type_translations.language_id', $languageId);
                                                    }
                                                ]);
                                        }
                                    ]);
                            },
                        ]);
                }
            ]);
    }

    public function fetchByUserAndStatus(int $userId, bool $completed, int $languageId, float $rate)
    {
        return $this->model->select('id', 'offer_id')
            ->where('user_id', $userId)
            ->where('completed', $completed)
            ->with([
                'cart_items' => function ($query) use ($languageId, $rate) {
                    return $this->fetchCartItemsQuery($query, $languageId, $rate);
                },
                'offer' => function ($query) {
                    $query->select('offers.id', 'cart_data', 'currencies.rate')
                        ->join('currencies', 'currencies.id', 'offers.currency_id');
                }
            ])
            ->first();
    }

    public function fetchByUserAndStatusWithOffer(int $userId, bool $completed)
    {
        return $this->model->select('id', 'offer_id')
            ->where('user_id', $userId)
            ->where('completed', $completed)
            ->with([
                'offer' => function ($query) {
                    $query->select('offers.id', 'cart_data', 'currencies.rate', 'shipping_cost', 'carrier')
                        ->join('currencies', 'currencies.id', 'offers.currency_id');
                }
            ])
            ->first();
    }

    public function fetchByIdForCartShare(int $cartId, int $languageId, float $rate)
    {
        return $this->model->select('id')
            ->where('id', $cartId)
            ->with([
                'cart_items' => function ($query) use ($languageId, $rate) {
                    return $this->fetchCartItemsQuery($query, $languageId, $rate);
                },
                'shared_cart' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'cart_id');
                }
            ])
            ->first();
    }

    public function selectOrCreate(int $userId, bool $completed, ?int $savedCartId)
    {
        $cart = $this->model->select('id')
            ->where('user_id', $userId)
            ->where('completed', $completed)
            ->first();

        if (!$cart) {
            $cart = $this->model->create([
                'user_id' => $userId,
                'completed' => $completed,
                'shared_cart_id' => $savedCartId,
            ]);
        } else {
            $cart->update([
                'shared_cart_id' => $savedCartId,
            ]);
        }

        return $cart->id;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function getActualCartIdForUser(int $userId, bool $completed)
    {
        return $this->model->select('id', 'shared_cart_id', 'offer_id')
            ->where('user_id', $userId)
            ->where('completed', $completed)
            ->with([
                'offer' => function ($query) {
                    $query->select('offers.id', 'cart_data', 'currencies.rate', 'shipping_cost', 'carrier')
                        ->join('currencies', 'currencies.id', 'offers.currency_id');
                }
            ])
            ->first();
    }

    public function deleteByUserIdAndStatus(int $userId, bool $completed)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('completed', $completed)
            ->delete();
    }

    public function deleteAllCart(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('completed', false)
            ->delete();
    }
}
