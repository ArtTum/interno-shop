<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\IndividualDocumentSetting\IndividualDocumentSettingFetchByFieldRequest;
use App\Http\Requests\ERP\IndividualDocumentSetting\IndividualDocumentSettingFetchRequest;
use App\Http\Requests\ERP\IndividualDocumentSetting\IndividualDocumentSettingUpdateRequest;
use App\Services\ERP\DocumentSetting\Individual\IndividualDocumentSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndividualDocumentSettingController extends Controller
{
    private IndividualDocumentSettingService $service;

    public function __construct(IndividualDocumentSettingService $service)
    {
        $this->service = $service;
    }

    public function fetch(IndividualDocumentSettingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('individual-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(IndividualDocumentSettingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('individual-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(IndividualDocumentSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('individual-document-settings-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('individual-document-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
