<?php

namespace App\Repositories\VendorCountry;

use App\Models\VendorCountry;
use App\Repositories\BaseRepository;

class VendorCountryRepository extends BaseRepository
{
    public function __construct(VendorCountry $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByVendorAndCountryIds(int $vendorId, array $countryIds): bool
    {
        return $this->model->where('vendor_id', $vendorId)->whereIn('all_country_id', $countryIds)->delete();
    }

    public function fetchIdsByVendorId(int $vendorId): array
    {
        return $this->model->select('all_country_id')->where('vendor_id', $vendorId)->pluck('all_country_id')->toArray();
    }
}
