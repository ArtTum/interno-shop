<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Country;
use App\Models\UploadLog;
use App\Models\User;
use App\Models\UserBillingAddress;
use App\Models\UserGroup;
use App\Models\UserShippingAddress;
use App\Repositories\Country\CountryRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserBillingAddress\UserBillingAddressRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use App\Repositories\UserShippingAddress\UserShippingAddressRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportUsers implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private UserRepository $userRepository;
    private UserBillingAddressRepository $userBillingAddressRepository;
    private UserShippingAddressRepository $userShippingAddressRepository;
    private UserGroupRepository $userGroupRepository;
    private CountryRepository $countryRepository;

    private $iniUserGroup;
    private $iniName;
    private $iniLastName;
    private $iniEmail;
    private $iniGtin;
    private $iniPassword;
    private $iniWCPassword;
    private $iniIsNewsletterSubscribed;
    private $iniBillingCountry;
    private $iniBillingName;
    private $iniBillingLastName;
    private $iniBillingCompany;
    private $iniBillingAddress;
    private $iniBillingAddress2;
    private $iniBillingCity;
    private $iniBillingZip;
    private $iniBillingState;
    private $iniBillingPhone;
    private $iniBillingEmail;
    private $iniBillingVATNumber;
    private $iniShippingCountry;
    private $iniShippingName;
    private $iniShippingLastName;
    private $iniShippingCompany;
    private $iniShippingAddress;
    private $iniShippingAddress2;
    private $iniShippingCity;
    private $iniShippingZip;
    private $iniShippingState;
    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->userRepository = new UserRepository(new User());
        $this->userBillingAddressRepository = new UserBillingAddressRepository(new UserBillingAddress());
        $this->userShippingAddressRepository = new UserShippingAddressRepository(new UserShippingAddress());
        $this->userGroupRepository = new UserGroupRepository(new UserGroup());
        $this->countryRepository = new CountryRepository(new Country());
    }

    public function collection(Collection $collection): void
    {
        setDBConnection($this->vendorKey);
        $invalidLines = 0;
        $totalLines = 0;
        $logsLoopNumber = 0;
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();
                if ($row == 0) {
                    if (count($data) !== count(UploadConstant::USERS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $this->iniUserGroup = !empty($data[1]) ? $data[1] : null;
                $this->iniName = !empty($data[2]) ? $data[2] : null;
                $this->iniLastName = !empty($data[3]) ? $data[3] : null;
                $this->iniEmail = !empty($data[4]) ? $data[4] : null;
                $this->iniGtin = !empty($data[5]) ? $data[5] : null;
                $this->iniPassword = !empty($data[6]) ? $data[6] : null;
                $this->iniWCPassword = !empty($data[7]) ? $data[7] : null;
                $this->iniIsNewsletterSubscribed = !empty($data[8]) ? $data[8] : null;
                $this->iniBillingCountry = !empty($data[9]) ? $data[9] : null;
                $this->iniBillingName = !empty($data[10]) ? $data[10] : null;
                $this->iniBillingLastName = !empty($data[11]) ? $data[11] : null;
                $this->iniBillingCompany = !empty($data[12]) ? $data[12] : null;
                $this->iniBillingAddress = !empty($data[13]) ? $data[13] : null;
                $this->iniBillingAddress2 = !empty($data[14]) ? $data[14] : null;
                $this->iniBillingCity = !empty($data[15]) ? $data[15] : null;
                $this->iniBillingZip = !empty($data[16]) ? $data[16] : null;
                $this->iniBillingState = !empty($data[17]) ? $data[17] : null;
                $this->iniBillingPhone = !empty($data[18]) ? $data[18] : null;
                $this->iniBillingEmail = !empty($data[19]) ? $data[19] : null;
                $this->iniBillingVATNumber = !empty($data[20]) ? $data[20] : null;
                $this->iniShippingCountry = !empty($data[21]) ? $data[21] : null;
                $this->iniShippingName = !empty($data[22]) ? $data[22] : null;
                $this->iniShippingLastName = !empty($data[23]) ? $data[23] : null;
                $this->iniShippingCompany = !empty($data[24]) ? $data[24] : null;
                $this->iniShippingAddress = !empty($data[25]) ? $data[25] : null;
                $this->iniShippingAddress2 = !empty($data[26]) ? $data[26] : null;
                $this->iniShippingCity = !empty($data[27]) ? $data[27] : null;
                $this->iniShippingZip = !empty($data[28]) ? $data[28] : null;
                $this->iniShippingState = !empty($data[29]) ? $data[29] : null;

                if (!empty($this->iniBillingCompany) && (strlen($this->iniBillingCompany) > 30)) {
                    $this->iniBillingCompany = mb_substr($this->iniBillingCompany, 0, 30);
                }

                if (!empty($this->iniShippingCompany) && (strlen($this->iniShippingCompany) > 30)) {
                    $this->iniShippingCompany = mb_substr($this->iniShippingCompany, 0, 30);
                }

                if (!empty($this->iniBillingAddress) && (strlen($this->iniBillingAddress) > 7)) {
                    $this->iniBillingAddress = mb_substr($this->iniBillingAddress, 0, 7);
                }

                if (!empty($this->iniShippingAddress) && (strlen($this->iniShippingAddress) > 7)) {
                    $this->iniShippingAddress = mb_substr($this->iniShippingAddress, 0, 7);
                }

                if (!empty($this->iniBillingAddress2) && (strlen($this->iniBillingAddress2) > 7)) {
                    $this->iniBillingAddress2 = mb_substr($this->iniBillingAddress2, 0, 7);
                }

                if (!empty($this->iniShippingAddress2) && (strlen($this->iniShippingAddress2) > 7)) {
                    $this->iniShippingAddress2 = mb_substr($this->iniShippingAddress2, 0, 7);
                }

                $user = null;
                if (!empty($this->iniEmail)) {
                    $user = $this->userRepository->fetchForUpload($this->iniEmail);
                }

                $newsLetterSubscribed = null;
                if ($this->iniIsNewsletterSubscribed === 'Yes') {
                    $newsLetterSubscribed = true;
                } else if ($this->iniIsNewsletterSubscribed === 'No') {
                    $newsLetterSubscribed = false;
                }

                if (!empty($this->iniUserGroup)) {
                    $userGroupId = $this->userGroupRepository->getIdByName($this->iniUserGroup);
                    if (!$userGroupId) $userGroupId = 11111111111;
                } else {
                    $userGroupId = null;
                }

                if (!empty($this->iniBillingCountry)) {
                    $billingCountryId = $this->countryRepository->getIdByCode($this->iniBillingCountry);
                    if (!$billingCountryId) $billingCountryId = 11111111111;
                } else {
                    $billingCountryId = null;
                }

                if (!empty($this->iniShippingCountry)) {
                    $shippingCountryId = $this->countryRepository->getIdByCode($this->iniShippingCountry);
                    if (!$shippingCountryId) $shippingCountryId = 11111111111;
                } else {
                    $shippingCountryId = null;
                }

                $validator = $this->validate($data, $userGroupId, $user, $billingCountryId, $shippingCountryId, $newsLetterSubscribed);

                if (!$validator['success']) {
                    foreach ($validator['errors'] as $error) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: {$error}",
                        ];
                    }
                    $invalidLines++;
                    continue;
                } else {
                    $billingAddressExists = $validator['billingAddressExists'];
                    $shippingAddressExists = $validator['shippingAddressExists'];
                }

                DB::beginTransaction();
                try {
                    $userPreparedArray = [
                        'user_group_id' => $userGroupId,
                        'name' => $this->iniName,
                        'last_name' => $this->iniLastName,
                        'email' => $this->iniEmail,
                        'gtin' => $this->iniGtin,
                        'wc_password' => $this->iniWCPassword,
                        'newsletter_subscribed' => $newsLetterSubscribed,
                    ];

                    if (!empty($this->iniPassword)) {
                        $userPreparedArray['password'] = Hash::make($this->iniPassword);
                    }

                    if ($this->importType == 1) {
                        $user = $this->userRepository->create($userPreparedArray);
                    } else if ($this->importType == 2) {
                        $this->userRepository->update('id', $user->id, $userPreparedArray);
                    } else if ($this->importType == 3) {
                        if (!$user) {
                            $user = $this->userRepository->create($userPreparedArray);
                        } else {
                            $this->userRepository->update('id', $user->id, $userPreparedArray);
                        }
                    }
                    if ($billingAddressExists) {
                        $userBillingAddressPreparedArray = [
                            'user_id' => $user->id,
                            'country_id' => $billingCountryId,
                            'name' => $this->iniBillingName,
                            'last_name' => $this->iniBillingLastName,
                            'company' => $this->iniBillingCompany,
                            'address' => $this->iniBillingAddress,
                            'address_2' => $this->iniBillingAddress2,
                            'city' => $this->iniBillingCity,
                            'zip' => $this->iniBillingZip,
                            'state' => $this->iniBillingState,
                            'phone' => $this->iniBillingPhone,
                            'email' => $this->iniBillingEmail,
                            'vat_number' => $this->iniBillingVATNumber,
                        ];

                        if (!$user->user_billing_address) {
                            $this->userBillingAddressRepository->insert(merge_dates_for_insert($userBillingAddressPreparedArray, now()));
                        } else {
                            $this->userBillingAddressRepository->update('id', $user->user_billing_address->id, $userBillingAddressPreparedArray);
                        }
                    }

                    if ($shippingAddressExists) {
                        $userShippingAddressPreparedArray = [
                            'user_id' => $user->id,
                            'country_id' => $shippingCountryId,
                            'name' => $this->iniShippingName,
                            'last_name' => $this->iniShippingLastName,
                            'company' => $this->iniShippingCompany,
                            'address' => $this->iniShippingAddress,
                            'address_2' => $this->iniShippingAddress2,
                            'city' => $this->iniBillingCity,
                            'zip' => $this->iniShippingZip,
                            'state' => $this->iniShippingState,
                        ];

                        if (!$user->user_shipping_address) {
                            $this->userShippingAddressRepository->insert(merge_dates_for_insert($userShippingAddressPreparedArray, now()));
                        } else {
                            $this->userShippingAddressRepository->update('id', $user->user_shipping_address->id, $userShippingAddressPreparedArray);
                        }
                    }
                    DB::commit();
                } catch (\Exception $exception) {
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                    ];
                    $invalidLines++;
                    DB::rollBack();
                    continue;
                }

                if ($logsLoopNumber === 50) {
                    if (!empty($logs)) $this->uploadLogRepository->insert($logs);
                    $logsLoopNumber = 0;
                }
            }

            if (!empty($logs)) $this->uploadLogRepository->insert($logs);

            $this->upload->update(
                [
                    'status' => UploadConstant::STATUSES['Completed'],
                    'total_lines' => $totalLines,
                    'invalid_lines' => $invalidLines,
                    'succeed_lines' => $totalLines - $invalidLines,
                ]
            );

            broadcast(new ReloadPagePublic('update-uploads-page'));
            broadcast(new ReloadPagePublic('update-users-page'));
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    private function validate(array $data, ?int $userGroupId, $user, ?int $billingCountryId, ?int $shippingCountryId, ?bool $newsLetterSubscribed): array
    {
        if ($this->importType == 2 && empty($user)) {
            return [
                'success' => false,
                'errors' => ["There is not user by this email"],
            ];
        } else if ($this->importType == 1 && !empty($user)) {
            return [
                'success' => false,
                'errors' => ["User by this email already exists"],
            ];
        }

        $emailExists = $this->userRepository->checkEmailExistsInActiveUsers(strtolower($this->iniEmail));

        if ($this->importType == 2 && $emailExists && $emailExists->id != $user->id) {
            return [
                'success' => false,
                'errors' => ["The email has already been taken"],
            ];
        } else if ($this->importType == 1 && $emailExists) {
            return [
                'success' => false,
                'errors' => ["The email has already been taken"],
            ];
        } else if ($this->importType == 3) {
            if (empty($user) && $emailExists) {
                return [
                    'success' => false,
                    'errors' => ["The email has already been taken"],
                ];
            } else if ($emailExists && $emailExists->id != $user->id) {
                return [
                    'success' => false,
                    'errors' => ["The email has already been taken"],
                ];
            }
        }

        $preparedValidationData = [
            'user_group_id' => $userGroupId,
            'name' => $this->iniName,
            'last_name' => $this->iniLastName,
            'email' => $this->iniEmail,
            'gtin' => $this->iniGtin,
            'newsletter_subscribed' => $newsLetterSubscribed,
        ];

        $rulesArray = [
            'user_group_id' => 'required|exists:user_groups,id',
            'name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'newsletter_subscribed' => 'required|boolean',
        ];

        if ($this->importType == 2) {
            $rulesArray['email'] = 'required|string|max:80|email';
            $rulesArray['gtin'] = 'nullable|string|max:50|unique:users,gtin,' . $user->id;
        } else if ($this->importType == 1) {
            $rulesArray['email'] = 'required|string|max:80|email';
            $rulesArray['gtin'] = 'nullable|string|max:50|unique:users,gtin';
        } else if ($this->importType == 3) {
            if (empty($user)) {
                $rulesArray['email'] = 'required|string|max:80|email';
                $rulesArray['gtin'] = 'nullable|string|max:80|unique:users,gtin';
            } else {
                $rulesArray['email'] = 'required|string|max:80|email';
                $rulesArray['gtin'] = 'nullable|string|max:80|unique:users,gtin,' . $user->id;
            }
        }

        $billingAddressExists = false;
        if ($user && $user->user_billing_address) {
            $billingAddressExists = true;
        } else {
            for ($i = 9; $i < 21; $i++) {
                if ($i !== 19 && $i !== 11 && $i !== 10 && !empty($data[$i])) {
                    $billingAddressExists = true;
                    break;
                }
            }
        }

        if ($billingAddressExists) {
            $preparedValidationData = array_merge($preparedValidationData, [
                'billing_address_country_id' => $billingCountryId,
                'billing_address_name' => $this->iniBillingName,
                'billing_address_last_name' => $this->iniBillingLastName,
                'billing_address_company' => $this->iniBillingCompany,
                'billing_address_address' => $this->iniBillingAddress,
                'billing_address_address_2' => $this->iniBillingAddress2,
                'billing_address_city' => $this->iniBillingCity,
                'billing_address_zip' => $this->iniBillingZip,
                'billing_address_state' => $this->iniBillingState,
                'billing_address_phone' => $this->iniBillingPhone,
                'billing_address_email' => $this->iniBillingEmail,
                'billing_address_vat_number' => $this->iniBillingVATNumber,
            ]);

            $rulesArray = array_merge($rulesArray, [
                'billing_address_country_id' => 'nullable|exists:countries,id',
                'billing_address_name' => 'required|string|max:150',
                'billing_address_last_name' => 'required|string|max:150',
                'billing_address_company' => 'nullable|string|max:150',
                'billing_address_address' => 'required|string|max:255',
                'billing_address_address_2' => 'nullable|string|max:150',
                'billing_address_city' => 'required|string|max:150',
                'billing_address_zip' => 'required|string|max:150',
                'billing_address_state' => 'nullable|string|max:150',
                'billing_address_phone' => 'required|string|max:150',
                'billing_address_email' => 'required|string|max:150|email',
                'billing_address_vat_number' => 'nullable|string|max:80',
            ]);
        }

        $shippingAddressExists = false;
        if ($user && $user->user_shipping_address) {
            $shippingAddressExists = true;
        } else {
            for ($i = 21; $i < 30; $i++) {
                if (!empty($data[$i])) {
                    $shippingAddressExists = true;
                    break;
                }
            }
        }
        if ($shippingAddressExists) {
            $preparedValidationData = array_merge($preparedValidationData, [
                'shipping_address_country_id' => $shippingCountryId,
                'shipping_address_name' => $this->iniShippingName,
                'shipping_address_last_name' => $this->iniShippingLastName,
                'shipping_address_company' => $this->iniShippingCompany,
                'shipping_address_address' => $this->iniShippingAddress,
                'shipping_address_address_2' => $this->iniShippingAddress2,
                'shipping_address_city' => $this->iniShippingCity,
                'shipping_address_zip' => $this->iniShippingZip,
                'shipping_address_state' => $this->iniShippingState,
            ]);


            $rulesArray = array_merge($rulesArray, [
                'shipping_address_country_id' => 'nullable|exists:countries,id',
                'shipping_address_name' => 'required|string|max:150',
                'shipping_address_last_name' => 'required|string|max:150',
                'shipping_address_company' => 'nullable|string|max:150',
                'shipping_address_address' => 'required|string|max:255',
                'shipping_address_address_2' => 'nullable|string|max:150',
                'shipping_address_city' => 'required|string|max:150',
                'shipping_address_zip' => 'required|string|max:150',
                'shipping_address_state' => 'nullable|string|max:150'
            ]);
        }

        $validator = Validator::make($preparedValidationData, $rulesArray);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->all(),
            ];
        } else {
            return [
                'success' => true,
                'shippingAddressExists' => $shippingAddressExists,
                'billingAddressExists' => $billingAddressExists,
            ];
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
