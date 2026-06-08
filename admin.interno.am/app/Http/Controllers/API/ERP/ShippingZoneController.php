<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\ShippingZone\ShippingZoneFetchByFieldRequest;
use App\Http\Requests\ERP\ShippingZone\ShippingZoneFetchRequest;
use App\Http\Requests\ERP\ShippingZone\ShippingZoneInsertRequest;
use App\Http\Requests\ERP\ShippingZone\ShippingZoneUpdateRequest;
use App\Services\ERP\Settings\ShippingZone\ShippingZoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingZoneController extends Controller
{
    private ShippingZoneService $service;

    public function __construct(ShippingZoneService $service)
    {
        $this->service = $service;
    }

    public function fetch(ShippingZoneFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(ShippingZoneFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->all());
        } catch (\Exception $exception) {
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function create(ShippingZoneInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->create($request->validated());
        } catch (\Exception $exception) {
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(ShippingZoneUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete(Request $request, $id): JsonResponse
    {
        try {
            return $this->service->delete((int) $id);
        } catch (\Exception $exception) {
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('shipping-zones-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
