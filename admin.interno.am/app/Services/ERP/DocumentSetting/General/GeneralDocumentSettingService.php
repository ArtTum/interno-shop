<?php

namespace App\Services\ERP\DocumentSetting\General;

use App\Constants\LanguageConstants;
use App\Models\UserActionHistory;
use App\Repositories\Country\CountryRepository;
use App\Repositories\GlobalDocumentSetting\GlobalDocumentSettingRepository;
use App\Repositories\GlobalDocumentSettingTranslation\GlobalDocumentSettingTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Services\ERP\DocumentSetting\General\Interfaces\GeneralDocumentSettingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GeneralDocumentSettingService implements GeneralDocumentSettingServiceInterface
{
    private GlobalDocumentSettingRepository $repository;
    private LanguageRepository $languageRepository;
    private GlobalDocumentSettingTranslationRepository $translationRepository;

    public function __construct(
        GlobalDocumentSettingRepository            $repository,
        LanguageRepository                         $languageRepository,
        GlobalDocumentSettingTranslationRepository $translationRepository,
        CountryRepository                          $countryRepository,
    )
    {
        $this->repository = $repository;
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->countryRepository = $countryRepository;
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, media_id";
        $data['base_language_id'] = $this->languageRepository->getBaseId();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByFieldWithLanguage('id', $data['id'], $select, $data)
        ]);
    }

    public function update(array $data): JsonResponse
    {
        UserActionHistory::create([
            'user_id' => Auth::user()?->id,
            'author' => Auth::user()?->name,
            'type' => 'update general',
            'description' => json_encode($data),
        ]);

        if ($data['language_id'] > -1) {
            $prepareData = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'footer_text' => $data['footer_text'],
                'seller_info' => $data['seller_info'],
                'rules' => $data['rules'],
            ];

            if ($data['translation_id']) {
                $this->translationRepository->update('id', $data['translation_id'], $prepareData);
            } else {
                $now = now()->toDateTimeString();
                $prepareData['global_document_setting_id'] = $data['id'];
                $prepareData['language_id'] = $data['language_id'];
                $prepareData['name'] = $data['name'];
                $prepareData['phone'] = $data['phone'];
                $prepareData['email'] = $data['email'];
                $prepareData['address'] = $data['address'];
                $prepareData['footer_text'] = $data['footer_text'];
                $prepareData['seller_info'] = $data['seller_info'];
                $prepareData['rules'] = $data['rules'];

                $prepareData = merge_dates_for_insert($prepareData, $now);
                $this->translationRepository->insert($prepareData);
            }
        } else {
            $prepareData = [
                'media_id' => $data['media_id'],
            ];
            $this->repository->update('id', $data['id'], $prepareData);
        }

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
                'relation_name' => 'global_document_setting_translation',
                'select' => "language_id",
                'where_field' => 'global_document_setting_id',
                'id' => $id,
            ];
        } else {
            $languageRelationArray = [];
        }

        $params['status'] = 1;

        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $params, [], [], $languageRelationArray);
        foreach ($languages as $language) {
            if ($language->global_document_setting_translation) $language->fulled = true;
        }
        $mergedLanguages = array_merge([LanguageConstants::GENERAL_OPTION], $languages->toArray());
        $countryOrdering = [
            'field' => 'code',
            'direction' => 'ASC',
        ];


        return response()->json([
            'success' => true,
            'customCountries' => $this->countryRepository->fetch("id as value, name as label, code, true as icon", [], $countryOrdering, [], [], []),
            'languages' => $mergedLanguages,
            'message' => 'Successfully reached!'
        ]);
    }

}
