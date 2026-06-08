<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\GeneralDocumentSetting\GeneralDocumentSettingFetchByFieldRequest;
use App\Http\Requests\ERP\GeneralDocumentSetting\GeneralDocumentSettingUpdateRequest;
use App\Services\ERP\DocumentSetting\General\GeneralDocumentSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralDocumentSettingController extends Controller
{
    private GeneralDocumentSettingService $service;

    public function __construct(GeneralDocumentSettingService $service)
    {
        $this->service = $service;
    }

    public function fetchByField(GeneralDocumentSettingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('general-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(GeneralDocumentSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('general-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchParams($request->all());
        } catch (\Exception $exception) {
            Log::channel('general-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
