<?php

namespace App\Repositories\ProductAffiliateProgram;

use App\Repositories\BaseRepository;
use App\Models\ProductAffiliateProgram;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductAffiliateProgramRepository extends BaseRepository
{
    public function __construct(ProductAffiliateProgram $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->with([
                'product' => function ($q) use ($params) {
                    $q->select('products.id', 'name', 'sku')
                        ->join('product_translations', 'products.id', 'product_translations.product_id')
                        ->join('product_variants', 'products.id', 'product_variants.product_id')
                        ->where('language_id', $params['language_id'])
                        ->where('is_main', true);
                },
                'affiliate_program' => function ($q) use ($params) {
                    $q->select('id', 'name');
                },
                'user' => function ($q) use ($params) {
                    $q->select('id', 'name', 'last_name');
                },
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByFieldWith(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}
