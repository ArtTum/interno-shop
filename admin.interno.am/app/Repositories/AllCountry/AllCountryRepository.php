<?php

namespace App\Repositories\AllCountry;

use App\Models\AllCountry;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AllCountryRepository extends BaseRepository
{
    public function __construct(AllCountry $model)
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

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins, ?array $relatedParams = []): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->when(!empty($relatedParams), function ($query) use ($relatedParams) {
                $query->with([
                    $relatedParams['relation_name'] => function ($query) use ($relatedParams) {
                        $query->select(DB::raw($relatedParams['select']))->where($relatedParams['where_field'], $relatedParams['id']);
                    }
                ]);
            })->get();
    }

    public function fetchForVendorParams(string $selectFields, ?int $vendorId)
    {
        return $this->model->select(DB::raw($selectFields))
            ->orderBy('code', 'asc')
            ->when($vendorId, function ($query) use ($vendorId) {
                $query->whereDoesntHave('vendor_country', function ($query) use ($vendorId) {
                    $query->when($vendorId, function ($query) use ($vendorId) {
                        $query->where('vendor_id', '!=', $vendorId);
                    });
                });
            })
            ->get();
    }

    public function fetchForVendorCheckoutParams(string $selectFields, ?int $vendorId)
    {
        return $this->model->select(DB::raw($selectFields))
            ->orderBy('code', 'asc')
//            ->when($vendorId, function ($query) use ($vendorId) {
//                $query->whereDoesntHave('vendor_checkout_country', function ($query) use ($vendorId) {
//                    $query->when($vendorId, function ($query) use ($vendorId) {
//                        $query->where('vendor_id', '!=', $vendorId);
//                    });
//                });
//            })
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

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}
