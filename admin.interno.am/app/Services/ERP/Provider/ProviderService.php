<?php

namespace App\Services\ERP\Provider;

use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\JsonResponse;

class ProviderService
{
    private UserRepository $userRepository;
    private GeneralSettingRepository $generalSettingRepository;

    public function __construct(
        UserRepository           $userRepository,
        GeneralSettingRepository $generalSettingRepository,
    )
    {
        $this->userRepository = $userRepository;
        $this->generalSettingRepository = $generalSettingRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $providerGroup = $this->generalSettingRepository->getByKey('provider_user_group_id', -1);

        if (!$providerGroup || !$providerGroup->base_value) {
            return response()->json([
                'success' => false,
                'errors' => [
                    ['The general setting "provider_user_group_id" is not set.']
                ],
            ]);
        }

        $data['type'] = '';
        $data['blocked'] = -1;
        $data['only_actives'] = -1;
        $data['user_group'] = $providerGroup->base_value;

        $select = "users.id, users.name, users.last_name, users.email, user_billing_addresses.company,
         user_billing_addresses.city, user_billing_addresses.phone, user_billing_addresses.vat_number, countries.name as country";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['users.name', 'users.last_name', 'users.email', 'user_billing_addresses.company'];
        $joins = [
            ['user_billing_addresses', 'users.id', '=', 'user_billing_addresses.user_id'],
            ['countries', 'user_billing_addresses.country_id', '=', 'countries.id']
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->userRepository->fetchProvider($select, $pagination, $ordering, $data, $searchFields, $joins),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->userRepository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, $joins)
            )
        ]);
    }
}
