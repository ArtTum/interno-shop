<?php

namespace App\Services\ERP\Vendor\Vendors;

use App\Constants\MarketplaceConstants;
use App\Constants\ShippingConstants;
use App\Constants\VendorConstants;
use App\Jobs\CountryTranslations;
use App\Repositories\Country\CountryRepository;
use App\Repositories\AllCountry\AllCountryRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\VendorCheckoutCountry\VendorCheckoutCountryRepository;
use App\Repositories\VendorOption\VendorOptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VendorService
{
    public function __construct(
        VendorRepository                $repository,
        AllCountryRepository            $allCountryRepository,
        CountryRepository               $countryRepository,
        VendorCheckoutCountryRepository $vendorCheckoutCountryRepository,
        VendorOptionRepository          $vendorOptionRepository,
    )
    {
        $this->repository = $repository;
        $this->allCountryRepository = $allCountryRepository;
        $this->countryRepository = $countryRepository;
        $this->vendorCheckoutCountryRepository = $vendorCheckoutCountryRepository;
        $this->vendorOptionRepository = $vendorOptionRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, name, db_name, db_server_ip, status, domain";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['name'];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, []),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, [])
            ),
            'statusesArray' => array_flip(VendorConstants::STATUSES)
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "vendors.id, name, db_name, db_server_ip, status, domain, b2b, loyalty_programs, shipping_and_labels, marketplaces, accounting_features,
        leads, abandoned_cart_emails, newsletter_system, dgd, cookie_management";

        $vendor = $this->repository->fetchByField('vendors.id', $data['id'], $select);
        $vendor->checkout_country_ids = $vendor->checkout_countries->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $vendor
        ]);
    }

    public function fetchParams(array $data): JsonResponse
    {
        $statusArray = collect(VendorConstants::STATUSES)->map(function ($value, $key) {
            return [
                'value' => $value,
                'label' => $key,
            ];
        })->values()->all();

        $shippingLabels = collect(ShippingConstants::SHIPPING_LABEL_SETTING_TYPES)->map(function ($item) {
            return [
                'label' =>  $item['name'],
                'value' => $item['id'],
            ];
        })->values()->all();

        $marketplaces = collect(MarketplaceConstants::ALL_MARKETPLACES)->map(function ($item) {
            return [
                'label' =>  $item['name'],
                'value' => $item['id'],
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'checkout_countries' => $this->allCountryRepository->fetchForVendorCheckoutParams("id, id as value, name as label, code, true as icon", $data['id'] ?? null),
            'statusArray' => $statusArray,
            'shippingLabels' => $shippingLabels,
            'marketplaces' => $marketplaces
        ]);
    }

    public function store(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $dbName = strtolower($data['name']);
            $dbName = str_replace(' ', '_', $dbName);

            $vendor = $this->repository->create([
                'name' => $data['name'],
                'db_name' => $dbName,
                'db_server_ip' => $data['db_server_ip'],
                'domain' => $data['domain'],
                'status' => VendorConstants::STATUSES['In development'],
            ]);

            $this->vendorOptionRepository->insert(merge_dates_for_insert(
                [
                    'vendor_id' => $vendor->id,
                    'b2b' => $data['b2b'],
                    'shipping_and_labels' => $data['shipping_and_labels'],
                    'marketplaces' => $data['marketplaces'],
                    'loyalty_programs' => $data['loyalty_programs'],
                    'accounting_features' => $data['accounting_features'],
                    'leads' => $data['leads'],
                    'abandoned_cart_emails' => $data['abandoned_cart_emails'],
                    'newsletter_system' => $data['newsletter_system'],
                    'dgd' => $data['dgd'],
                    'cookie_management' => $data['cookie_management'],
                ], now()
            ));

            $this->countriesStoreLogic($data['checkout_country_ids'], $vendor->id, $dbName, 'vendorCheckoutCountryRepository', 'countryRepository');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully created!'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            throw new \Exception($exception);
        }
    }

    public function update(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->repository->update('id', $data['id'], [
                'status' => $data['status'],
                'domain' => $data['domain'],
                'db_server_ip' => $data['db_server_ip'],
            ]);

            $this->vendorOptionRepository->updateOrCreate(
                [
                    'vendor_id' => $data['id'],
                ],
                [
                    'b2b' => $data['b2b'],
                    'shipping_and_labels' => $data['shipping_and_labels'],
                    'marketplaces' => $data['marketplaces'],
                    'loyalty_programs' => $data['loyalty_programs'],
                    'accounting_features' => $data['accounting_features'],
                    'leads' => $data['leads'],
                    'abandoned_cart_emails' => $data['abandoned_cart_emails'],
                    'newsletter_system' => $data['newsletter_system'],
                    'dgd' => $data['dgd'],
                    'cookie_management' => $data['cookie_management'],
                ]
            );

            $this->countriesUpdateLogic($data, 'checkout_country_ids', 'vendorCheckoutCountryRepository', 'countryRepository');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated!'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            throw new \Exception($exception);
        }
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }

    private function countriesStoreLogic(
        array  $ids,
        int    $vendorId,
        string $dbName,
        string $vendorRepositoryName,
        string $storeRepositoryName,
    ): void
    {
        $vendorPrepared = [];
        $storePrepared = [];
        foreach ($ids as $id) {
            $vendorPrepared[] = merge_dates_for_insert([
                'vendor_id' => $vendorId,
                'all_country_id' => $id,
            ], now());

            $allCountry = $this->allCountryRepository->fetchByField('id', $id, '*');
            $storePrepared[] = merge_dates_for_insert([
                'all_country_id' => $id,
                'code' => $allCountry->code,
                'name' => $allCountry->name,
            ], now());
        }

        if (!empty($vendorPrepared)) {
            call_user_func_array([$this->$vendorRepositoryName, 'insert'], [$vendorPrepared]);
        }
        if (!empty($storePrepared)) {
            call_user_func_array([$this->$storeRepositoryName, 'insert'], [$storePrepared, $dbName]);
        }

        if (!empty($storePrepared)) {
            $codes = array_column($storePrepared, 'code');
            CountryTranslations::dispatch($dbName, $codes);
        }
    }

    private function countriesUpdateLogic(array $data, string $countryIdsKey, string $vendorRepositoryName, string $storeRepositoryName): void
    {
        $currentAllCountryIds = call_user_func_array([$this->$vendorRepositoryName, 'fetchIdsByVendorId'], [$data['id']]);
        $countryToAdd = array_diff($data[$countryIdsKey], $currentAllCountryIds);

        $vendorPrepared = [];
        $storePrepared = [];
        foreach ($countryToAdd as $countryId) {
            $vendorPrepared[] = merge_dates_for_insert([
                'vendor_id' => $data['id'],
                'all_country_id' => $countryId,
            ], now());

            $allCountry = $this->allCountryRepository->fetchByField('id', $countryId, '*');
            $storePrepared[] = merge_dates_for_insert([
                'all_country_id' => $countryId,
                'code' => $allCountry->code,
                'name' => $allCountry->name,
            ], now());
        }

        if (!empty($vendorPrepared)) {
            call_user_func_array([$this->$vendorRepositoryName, 'insert'], [$vendorPrepared]);
        }
        if (!empty($storePrepared)) {
            call_user_func_array([$this->$storeRepositoryName, 'insert'], [$storePrepared, $data['db_name']]);
        }

        $countriesToRemove = array_diff($currentAllCountryIds, $data[$countryIdsKey]);
        if (!empty($countriesToRemove)) {
            call_user_func_array([$this->$vendorRepositoryName, 'deleteByVendorAndCountryIds'], [$data['id'], $countriesToRemove]);
            call_user_func_array([$this->$storeRepositoryName, 'bulkDeleteByAllCountryIds'], [$countriesToRemove, $data['db_name']]);
        }

        if (!empty($storePrepared)) {
            $codes = array_column($storePrepared, 'code');
            CountryTranslations::dispatch($data['db_name'], $codes);
        }
    }
}
