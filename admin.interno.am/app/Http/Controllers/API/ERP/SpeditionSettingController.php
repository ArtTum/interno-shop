<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\SpeditionSetting\SpeditionSettingFetchByFieldRequest;
use App\Http\Requests\ERP\SpeditionSetting\SpeditionSettingUpdateRequest;
use App\Services\ERP\Settings\SpeditionSetting\SpeditionSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SpeditionSettingController extends Controller
{
    private SpeditionSettingService $service;

    public function __construct(SpeditionSettingService $service)
    {
        $this->service = $service;
    }

    public function fetchByField(SpeditionSettingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('spedition-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(SpeditionSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('spedition-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchParams(): JsonResponse
    {
        try {
            return $this->service->fetchParams();
        } catch (\Exception $exception) {
            Log::channel('spedition-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
