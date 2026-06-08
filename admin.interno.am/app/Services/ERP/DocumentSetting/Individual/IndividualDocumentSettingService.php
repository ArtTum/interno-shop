<?php

namespace App\Services\ERP\DocumentSetting\Individual;

use App\Constants\InvoiceConstants;
use App\Constants\LanguageConstants;
use App\Constants\OrderConstants;
use App\Models\UserActionHistory;
use App\Repositories\GlobalDocumentSetting\GlobalDocumentSettingRepository;
use App\Repositories\GlobalDocumentSettingTranslation\GlobalDocumentSettingTranslationRepository;
use App\Services\ERP\DocumentSetting\Individual\Interfaces\IndividualDocumentSettingServiceInterface;
use App\Repositories\DocumentSetting\DocumentSettingRepository;
use App\Repositories\DocumentSettingTranslation\DocumentSettingTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IndividualDocumentSettingService implements IndividualDocumentSettingServiceInterface
{
    public function __construct(
        DocumentSettingRepository                  $repository,
        LanguageRepository                         $languageRepository,
        DocumentSettingTranslationRepository       $translationRepository,
        GlobalDocumentSettingRepository            $globalDocumentSettingRepository,
        GlobalDocumentSettingTranslationRepository $globalDocumentSettingTranslationRepository,
    )
    {
        $this->repository = $repository;
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->globalDocumentSettingRepository = $globalDocumentSettingRepository;
        $this->globalDocumentSettingTranslationRepository = $globalDocumentSettingTranslationRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "document_settings.id, name, generate_on_new_order, generate_invoice_also_in_base_language, create_automatically_after_refunding, statuses";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];

        $data['base_language_id'] = $this->languageRepository->getBaseId();

        $searchFields = [
            [
                'fields' => ['name'],
                'relation_name' => 'document_setting_translations'
            ]
        ];

        $joins = [];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, $joins),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, $joins)
            ),
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, name, statuses, display_invoice_date, display_email_address, display_phone_number, generate_on_new_order, generate_invoice_also_in_base_language, create_automatically_after_refunding, display_credit_note_date, use_positive_prices, show_original_invoice_number";
        $data['base_language_id'] = $this->languageRepository->getBaseId();
        $selectGeneral = "id, media_id";
        $dataGeneral['languageId'] = $this->languageRepository->getBaseId();
        $dataGeneral['base_language_id'] = $this->languageRepository->getBaseId();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByFieldWithLanguage('id', $data['id'], $select, $data),
            'data_general' => $this->globalDocumentSettingRepository->fetchByFieldWithLanguage('id', 1, $selectGeneral, $dataGeneral),
        ]);
    }

    public function update(array $data): JsonResponse
    {
        UserActionHistory::create([
            'user_id' => Auth::user()?->id,
            'author' => Auth::user()?->name,
            'type' => 'update individual',
            'description' => json_encode($data),
        ]);

        if ($data['language_id'] > -1) {
            $prepareData = [
                'document_title' => $data['document_title'],
                'number_format_prefix' => $data['number_format_prefix'],
                'text_below_title' => $data['text_below_title'],
            ];

            if ($data['translation_id']) {
                $this->translationRepository->update('id', $data['translation_id'], $prepareData);
            } else {
                $now = now()->toDateTimeString();
                $prepareData['document_setting_id'] = $data['id'];
                $prepareData['language_id'] = $data['language_id'];

                $prepareData = merge_dates_for_insert($prepareData, $now);
                $this->translationRepository->insert($prepareData);
            }
        } else {
            $prepareData = [
                'statuses' => $data['statuses'],
                'display_invoice_date' => $data['display_invoice_date'],
                'display_email_address' => $data['display_email_address'],
                'display_phone_number' => $data['display_phone_number'],
                'generate_on_new_order' => $data['generate_on_new_order'],
                'generate_invoice_also_in_base_language' => $data['generate_invoice_also_in_base_language'],
                'create_automatically_after_refunding' => $data['create_automatically_after_refunding'],
                'display_credit_note_date' => $data['display_credit_note_date'],
                'use_positive_prices' => $data['use_positive_prices'],
                'show_original_invoice_number' => $data['show_original_invoice_number'],
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
                'relation_name' => 'document_setting_translation',
                'select' => "language_id",
                'where_field' => 'document_setting_id',
                'id' => $id,
            ];
        } else {
            $languageRelationArray = [];
        }

        $params['status'] = 1;

        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $params, [], [], $languageRelationArray);
        foreach ($languages as $language) {
            if ($language->document_setting_translation) $language->fulled = true;
        }
        $mergedLanguages = array_merge([LanguageConstants::GENERAL_OPTION], $languages->toArray());

        $orderStatuses = collect(OrderConstants::STATUSES_FOR_DOCUMENTS)->map(function ($value, $key) {
            return [
                'value' => $value,
                'label' => $key,
            ];
        })->values()->all();

        $invoiceDate = collect(InvoiceConstants::INVOICE_DATE)->map(function ($value, $key) {
            return [
                'value' => $value,
                'label' => $key,
            ];
        })->values()->all();

        $creditNoteDate = collect(InvoiceConstants::CREDIT_NOTE_DATE)->map(function ($value, $key) {
            return [
                'value' => $value,
                'label' => $key,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'languages' => $mergedLanguages,
            'orderStatusesParams' => $orderStatuses,
            'invoiceDate' => $invoiceDate,
            'creditNoteDate' => $creditNoteDate,
        ]);
    }
}
