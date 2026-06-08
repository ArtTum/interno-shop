<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\MarketplaceSetting\MarketplaceSettingFetchByFieldRequest;
use App\Http\Requests\ERP\MarketplaceSetting\MarketplaceSettingFetchRequest;
use App\Http\Requests\ERP\MarketplaceSetting\MarketplaceSettingUpdateRequest;
use App\Services\ERP\Marketplaces\MarketplaceSetting\MarketplaceSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceSettingController extends Controller
{
    private MarketplaceSettingService $service;

    public function __construct(MarketplaceSettingService $service)
    {
        $this->service = $service;
    }

    public function fetch(MarketplaceSettingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(MarketplaceSettingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(MarketplaceSettingUpdateRequest $request): JsonResponse
    {
        try {

            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchParams($request->all());
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
