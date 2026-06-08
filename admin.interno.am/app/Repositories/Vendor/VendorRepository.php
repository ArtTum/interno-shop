<?php

namespace App\Repositories\Vendor;

use App\Constants\VendorConstants;
use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $model)
    {
        $this->model = $model;
    }

    public static function getInstance()
    {
        return app(self::class);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with('checkout_countries')
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->leftJoin('vendor_options', 'vendors.id', '=', 'vendor_options.vendor_id')
            ->where($whereField, $whereValue)
            ->with([
                'checkout_countries' => function ($query) {
                    $query->select('all_countries.id');
                },
            ])
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

    public function fetchForPreparingDB(?string $vendorConnection)
    {
        return $this->model->where('status', VendorConstants::STATUSES['Active'])
            ->when($vendorConnection, function ($query) use ($vendorConnection) {
                $query->where('db_name', $vendorConnection);
            })
            ->get();
    }

    public function fetchForSiteMap(?string $vendorConnection)
    {
        return $this->model->select('db_name')
            ->where('status', VendorConstants::STATUSES['Active'])
            ->when($vendorConnection, function ($query) use ($vendorConnection) {
                $query->where('db_name', $vendorConnection);
            })
            ->get();
    }

    public function vendorB2B(string $vendorKey)
    {
        $b2b = Cache::tags(["{$vendorKey}_general"])->get("{$vendorKey}:b2b");

        if (!$b2b) {
            $b2b = $this->model->select('b2b')
                ->join('vendor_options', 'vendor_options.vendor_id', 'vendors.id')
                ->where('db_name', $vendorKey)
                ->value('b2b');

            Cache::tags(["{$vendorKey}_general"])->put("{$vendorKey}:b2b", $b2b, 1000000);
        }

        return $b2b;
    }

    public function fetchVendorsForSwitch(string $currentVendor)
    {
        return $this->model->select('db_name')
            ->where('db_name', '!=', $currentVendor)
            ->pluck('db_name')
            ->toArray();
    }
}
