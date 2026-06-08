<?php

namespace App\Services\ERP\Settings\General;

use App\Constants\GeneralSettingConstants;
use App\Constants\LanguageConstants;
use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\GeneralSettingTranslation\GeneralSettingTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Services\ERP\Settings\General\Interfaces\GeneralServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeneralService implements GeneralServiceInterface
{
    public function __construct(
        GeneralSettingRepository            $repository,
        LanguageRepository                  $languageRepository,
        GeneralSettingTranslationRepository $generalSettingTranslationRepository,
        GeneralSettingRepository            $generalSettingRepository,
    )
    {
        $this->repository = $repository;
        $this->languageRepository = $languageRepository;
        $this->generalSettingTranslationRepository = $generalSettingTranslationRepository;
        $this->generalSettingRepository = $generalSettingRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "general_settings.id, value, `key`";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];

        $baseLanguageId = $this->languageRepository->getBaseId();
        $data['base_language_id'] = $data['language_id'] > 0 ? $data['language_id'] : $baseLanguageId;

        $searchFields = [
            [
                'fields' => ['`key`', 'value'],
                'relation_name' => 'general_setting_translations'
            ]
        ];

        $joins = [];

        $groups = collect(GeneralSettingConstants::GENERAL_SETTINGS_GROUPS)->map(function ($value, $key) {
            return [
                'value' => $key,
                'label' => $value,
            ];
        })->values()->all();
        $groups = array_merge([['value' => -1, 'label' => 'All']], $groups);

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, $joins),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, $joins)
            ),
            'groups' => $groups,
            'languages' => $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], ['status' => 1], [], []),
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, `key`, value";
        $data['base_language_id'] = $this->languageRepository->getBaseId();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByFieldWithLanguage('id', $data['id'], $select, $data),
        ]);
    }

    public function update(array $data): JsonResponse
    {
        if ($data['language_id'] > -1) {
            $prepareData = [
                'value' => $data['lang_value'],
            ];

            if ($data['translation_id']) {
                $this->generalSettingTranslationRepository->update('id', $data['translation_id'], $prepareData);
            } else {
                $now = now()->toDateTimeString();
                $prepareData['general_setting_id'] = $data['id'];
                $prepareData['language_id'] = $data['language_id'];

                $prepareData = merge_dates_for_insert($prepareData, $now);
                $this->generalSettingTranslationRepository->insert($prepareData);
            }
        } else {
            $prepareData = [
                'value' => $data['value'],
            ];

            $this->repository->update('id', $data['id'], $prepareData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function deleteCache(array $data, Request $request): JsonResponse
    {
        $vendorKey = $request->header('VendorKey');
        $user = Auth::user();

        if (!$vendorKey) {
            $vendorKey = $request->input('vendor_key');
        }

        Log::channel('general-cache-cleared-info')->info("User ID: {$user->id}, User name: {$user->name} {$user->last_name}, vendor: {$vendorKey}, {$data['cache_key']}");


        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function fetchParams(array $data): JsonResponse
    {
        if (array_key_exists('id', $data)) {
            $id = (int)$data['id'];
            $languageRelationArray = [
                'relation_name' => 'general_setting_translation',
                'select' => "language_id",
                'where_field' => 'general_setting_id',
                'id' => $id,
            ];
        } else {
            $languageRelationArray = [];
        }

        $params['status'] = 1;

        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $params, [], [], $languageRelationArray);
        foreach ($languages as $language) {
            if ($language->general_setting_translation) $language->fulled = true;
        }
        $mergedLanguages = array_merge([LanguageConstants::GENERAL_OPTION], $languages->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'languages' => $mergedLanguages,
        ]);
    }
}
