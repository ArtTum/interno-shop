<?php

namespace App\Services\ERP\Users\User;

use App\Constants\UploadConstant;
use App\Export\ERP\DefaultExport;
use App\Jobs\UploadUsers;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\CustomerGroup\CustomerGroupRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\MemberGroup\MemberGroupRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\Upload\UploadRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserAffiliate\UserAffiliateRepository;
use App\Repositories\UserBillingAddress\UserBillingAddressRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use App\Repositories\UserShippingAddress\UserShippingAddressRepository;
use App\Services\ERP\Users\User\Interfaces\UserServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService implements UserServiceInterface
{
    private string $exportFilePath = 'app/public/exports';
    private string $exportFileName = 'users.xlsx';

    public function __construct(
        UserRepository                $repository,
        UserGroupRepository           $userGroupRepository,
        CountryRepository             $countryRepository,
        UserBillingAddressRepository  $userBillingAddressRepository,
        UserShippingAddressRepository $userShippingAddressRepository,
        UploadRepository              $uploadRepository,
        CustomerGroupRepository       $customerGroupRepository,
        CurrencyRepository            $currencyRepository,
        OrderRepository               $orderRepository,
        OrderItemRepository           $orderItemRepository,
        LanguageRepository            $languageRepository,
        MemberGroupRepository         $memberGroupRepository,
        UserAffiliateRepository       $userAffiliateRepository,
    )
    {
        $this->repository = $repository;
        $this->userGroupRepository = $userGroupRepository;
        $this->countryRepository = $countryRepository;
        $this->userBillingAddressRepository = $userBillingAddressRepository;
        $this->userShippingAddressRepository = $userShippingAddressRepository;
        $this->uploadRepository = $uploadRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->currencyRepository = $currencyRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->languageRepository = $languageRepository;
        $this->memberGroupRepository = $memberGroupRepository;
        $this->userAffiliateRepository = $userAffiliateRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "users.id, users.source, user_group_id, users.name, blocked, last_name, email, balance, IF(blocked = false, '-', 'Blocked') as status_text, user_groups.name AS group_name, superadmin";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = [DB::raw("CONCAT(users.name, ' ', users.last_name)"), 'users.email'];
        $joins = [['user_groups', 'user_groups.id', '=', 'users.user_group_id']];

        $data['user_group_id_customer'] = $this->userGroupRepository->getIdByGroup('customer');
        $data['user_group_id_affiliate'] = $this->userGroupRepository->getIdByGroup('affiliate');

        if (!$data['user_group_id_affiliate']) {
            $data['user_group_id_affiliate'] = 99999999;
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchUser($select, $pagination, $ordering, $data, $searchFields, $joins),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, $joins)
            )
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "users.id, user_group_id, users.name, blocked, last_name, email, gtin, blocked, superadmin, ip, ip_expires_at, balance, newsletter_subscribed, check_client_certificate";
        $response = $this->repository->fetchByField('id', $data['id'], $select, ['user_billing_address', 'user_shipping_address', 'user_affiliate']);

        if (!empty($response->ip_expires_at)) {
            $fullDate = $response->ip_expires_at;
            $response->ip_expires_at = Carbon::parse($fullDate)->toDateString();
            $response->time = Carbon::parse($fullDate)->format('H:i');
        }
        $response['transactions'] = null;


        $response['base_currency_symbol'] = $this->currencyRepository->getBaseOrFirst();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $response,
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userData = [
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'user_group_id' => $data['user_group_id'],
                'email' => $data['email'],
                'blocked' => $data['blocked'],
                'password' => Hash::make($data['password']),
            ];

            $this->repository->create($userData);


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

    public function interests(array $data): JsonResponse
    {
        if ($data['interest'] === 'products') {
            $ids = $this->orderItemRepository->fetchInfoForCustomerInterestsProducts($data['user_id'], $this->languageRepository->getBaseId());
        } else if ($data['interest'] === 'categories') {
            $ids = $this->orderItemRepository->fetchInfoForCustomerInterestsCategories($data['user_id'], $this->languageRepository->getBaseId());
        }

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'message' => 'Successfully reached!'
        ]);
    }

    public function update(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userUpdatingData = [
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'user_group_id' => $data['user_group_id'],
                'gtin' => $data['gtin'] ?? null,
                'email' => $data['email'],
                'blocked' => $data['blocked'],
                'newsletter_subscribed' => $data['newsletter_subscribed'],
                'check_client_certificate' => $data['check_client_certificate'],
            ];

            $userUpdatingData['ip'] = $data['ip'];
            $userUpdatingData['ip_expires_at'] = !empty($data['ip_expires_at']) ? "{$data['ip_expires_at']} {$data['time']}:00" : null;

            if (!empty($data['password'])) {
                $userUpdatingData['password'] = Hash::make($data['password']);
            }

            $this->repository->update('id', $data['id'], $userUpdatingData);
            $now = now()->toDateTimeString();
            if (!empty($data['billing_addresses'])) {
                $billingAddresses = merge_dates_for_insert($data['billing_addresses'], $now);
                $billingAddresses['user_id'] = $data['id'];
                $this->userBillingAddressRepository->insertOrUpdate('user_id', $data['id'], $billingAddresses);
            }

            if (!empty($data['shipping_addresses'])) {
                $shippingAddresses = merge_dates_for_insert($data['shipping_addresses'], $now);
                $shippingAddresses['user_id'] = $data['id'];
                $this->userShippingAddressRepository->insertOrUpdate('user_id', $data['id'], $shippingAddresses);
            }



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

    public function fetchParams(): JsonResponse
    {
        $userGroups = $this->userGroupRepository->fetch("id as value, name as label", [], [], [], [], []);
//        $memberGroups = $this->memberGroupRepository->fetch("id as value, name as label", [], [], [], [], []);
//        $customerGroups = $this->customerGroupRepository->fetchCustomerGroups("id as value, name as label");
//        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], ['status'], [], [], []);

        return response()->json(['success' => true,
            'userGroups' => $userGroups,
            'customerGroups' => [],
            'memberGroups' => [],
            'countries' => [],
            'languages' => [],
            'message' => 'Successfully reached!']);
    }

    public function upload(array $data, Request $request): JsonResponse
    {
        $vendorKey = $request->header('VendorKey');

        if (!$vendorKey) {
            $vendorKey = $request->input('vendor_key');
        }

        $file = $data['file'];
        $fileName = $file->getClientOriginalName();

        $filePath = $file->storeAs('uploads/' . $vendorKey . '/temp', $fileName, 'public');
        $user = Auth::user();

        $upload = $this->uploadRepository->create([
            'name' => $fileName,
            'type' => UploadConstant::TYPES['Users'],
            'status' => UploadConstant::STATUSES['In process'],
            'total_lines' => 0,
            'invalid_lines' => 0,
            'succeed_lines' => 0,
            'uploaded_by' => $user->name . ' ' . $user->last_name,
            'user_id' => $user->id,
        ]);

        UploadUsers::dispatch($filePath, $upload, $data['import_type'], $vendorKey);

        return response()->json([
            'success' => true,
            'message' => 'Successfully uploaded!'
        ]);
    }

    public function autocomplete(array $data): JsonResponse
    {

        $result = $this->repository->autocomplete($data['field'], $data['term'], $data['alreadySelectIds'] ?? []);


        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Successfully reached!'
        ]);
    }

    public function export(array $data): BinaryFileResponse
    {
        if (!file_exists(storage_path($this->exportFilePath))) {
            mkdir(storage_path($this->exportFilePath), 777, true);
        }

        if (file_exists(storage_path("$this->exportFilePath/$this->exportFileName"))) {
            unlink(storage_path("$this->exportFilePath/$this->exportFileName"));
        }
        $data['user_group_id_customer'] = $this->userGroupRepository->getIdByGroup('customer');

        $collectData = [];

        $headers = [
            UploadConstant::USERS_FILE_HEADERS['id'],
            UploadConstant::USERS_FILE_HEADERS['user_group'],
            UploadConstant::USERS_FILE_HEADERS['name'],
            UploadConstant::USERS_FILE_HEADERS['last_name'],
            UploadConstant::USERS_FILE_HEADERS['email'],
            UploadConstant::USERS_FILE_HEADERS['gtin'],
            UploadConstant::USERS_FILE_HEADERS['password'],
            UploadConstant::USERS_FILE_HEADERS['wc_password'],
            UploadConstant::USERS_FILE_HEADERS['newsletter_subscribed'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_country'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_name'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_last_name'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_company'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_address'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_address_2'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_city'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_zip'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_state'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_phone'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_email'],
            UploadConstant::USERS_FILE_HEADERS['billing_address_vat_number'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_country'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_name'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_last_name'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_company'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_address'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_address_2'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_city'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_zip'],
            UploadConstant::USERS_FILE_HEADERS['shipping_address_state'],
        ];

        if (!filter_var($data['justTemplate'], FILTER_VALIDATE_BOOLEAN)) {
            if (filter_var($data['byPageFilter'], FILTER_VALIDATE_BOOLEAN)) {
                $select = "users.*";
                $pagination = prepare_pagination_array($data['page'], $data['per_page']);
                $ordering = [
                    'field' => $data['ordering_field'],
                    'direction' => $data['ordering_direction']
                ];
                $searchFields = [DB::raw("CONCAT(users.name, ' ', users.last_name)"), 'users.email'];
                $joins = [];

                $usersFullList = $this->repository->fetchForExportByFilters($select, $pagination, $ordering, $data, $searchFields, $joins);
                $usersFullListChunked = array_chunk($usersFullList, 50);
            } else {
                $usersFullList = $this->repository->fetchForExport($data);
                $usersFullListChunked = array_chunk($usersFullList->toArray(), 50);
            }


            foreach ($usersFullListChunked as $users) {
                foreach ($users as $user) {
                    $collectData[] = [
                        $user['id'],
                        $user['user_group']['name'],
                        $user['name'],
                        $user['last_name'],
                        $user['email'],
                        $user['gtin'],
                        '',
                        '',
                        $user['newsletter_subscribed'] ? 'Yes' : 'No',
                        !empty($user['user_billing_address']['country']['code']) ? $user['user_billing_address']['country']['code'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['name'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['last_name'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['company'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['address'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['address_2'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['city'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['zip'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['state'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['phone'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['email'] : null,
                        $user['user_billing_address'] ? $user['user_billing_address']['vat_number'] : null,
                        !empty($user['user_shipping_address']['country']['code']) ? $user['user_shipping_address']['country']['code'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['name'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['last_name'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['company'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['address'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['address_2'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['city'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['zip'] : null,
                        $user['user_shipping_address'] ? $user['user_shipping_address']['state'] : null,
                    ];
                }
            }
        }

        return Excel::download(new DefaultExport(collect($collectData), $headers), $this->exportFileName);
    }
}
