<?php

namespace App\Services\ERP\General;

use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Vendor\VendorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GeneralInfoService
{
    public function __construct(
        LanguageRepository       $repository,
        VendorRepository         $vendorRepository,
        UserRepository           $userRepository,
        GeneralSettingRepository $generalSettingRepository
    )
    {
        $this->languageRepository = $repository;
        $this->vendorRepository = $vendorRepository;
        $this->userRepository = $userRepository;
        $this->generalSettingRepository = $generalSettingRepository;
    }

    public function fetch(): JsonResponse
    {
        $vendorKey = Auth::user()->getConnectionName();
        $languageParams['status'] = 1;
        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $languageParams, [], [], []);
        $vendor = $this->vendorRepository->fetchByField('db_name', $vendorKey,
            'vendors.id, b2b, loyalty_programs, shipping_and_labels, marketplaces, accounting_features, leads, abandoned_cart_emails, newsletter_system, dgd, cookie_management'
        );

        $logoInfo = $this->generalSettingRepository->getByKeyWithoutLanguage('admin_panel_logo', $vendorKey);

        return response()->json([
            'success' => true,
            'languages' => $languages,
            'logo' => $logoInfo,
            'vendor' => $vendor,
            'base_language_id' => $this->languageRepository->getBaseId(),
            'base_url' => GeneralSettingRepository::getInstance()->getByKeyWithoutLanguage('admin_domain', Auth::user()->getConnectionName()),
            'message' => 'Successfully reached!',
        ]);
    }

    public function fetchVendorsForSwitch(string $vendorKey): JsonResponse
    {


        return response()->json([
            'success' => true,
            'vendors' => '',
        ]);
    }
}
