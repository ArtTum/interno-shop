<?php

namespace App\Repositories\ProductMultiselectOption;

use App\Repositories\BaseRepository;
use App\Models\ProductMultiselectOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductMultiselectOptionRepository extends BaseRepository
{
    public function __construct(ProductMultiselectOption $model)
    {
        $this->model = $model;
    }

    public function getIdsByMultiselectId(int $multiselectId)
    {
        return $this->model->select('id')->where('product_multiselect_id', $multiselectId)->pluck('id')->toArray();
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function updateOrCreate(array $checkArray, array $values)
    {
        return $this->model->updateOrCreate($checkArray, $values);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function pricesSumByProductMultiselectIds(array $productMultiselectIds, float $rate)
    {
        return $this->model->select('additional_price')
            ->whereIn('id', $productMultiselectIds)
            ->sum(DB::raw("additional_price * {$rate}"));
    }

    public function fetchOptionsForParentItems(array $productMultiselectIds)
    {
        return $this->model->select('id')
            ->whereIn('id', $productMultiselectIds)
            ->select('product_multiselect_options.id', 'product_multiselect_id', 'additional_price')
            ->with([
                'product_multiselect_option_parents' => function ($q) {
                    return $q->select('product_multiselect_option_id', 'items.sku', 'quantity', 'items.id', 'net_weight', 'gross_weight', 'un_numbers', 'production_price',
                        'category_id', 'regular_price', 'name')
                        ->join('items', 'items.id', '=', 'product_multiselect_option_parents.item_id');
                }
            ])
            ->get();
    }
}
