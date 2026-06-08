<?php

namespace App\Repositories\VendorCheckoutCountry;

use App\Models\VendorCheckoutCountry;
use App\Repositories\BaseRepository;

class VendorCheckoutCountryRepository extends BaseRepository
{
    public function __construct(VendorCheckoutCountry $model)
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
