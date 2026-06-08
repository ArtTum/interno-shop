<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\MediaSetting\MediaSettingFetchRequest;
use App\Http\Requests\ERP\MediaSetting\MediaSettingUpdateRequest;
use App\Services\ERP\Settings\MediaSetting\MediaSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MediaSettingController extends Controller
{
    private MediaSettingService $service;

    public function __construct(MediaSettingService $service)
    {
        $this->service = $service;
    }

    public function fetch(MediaSettingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('media-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(MediaSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('media-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
