<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Report\Controlling\ControllingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ControllingController extends Controller
{
    private ControllingService $service;

    public function __construct(ControllingService $service)
    {
        $this->service = $service;
    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->all());
        } catch (\Exception $exception) {
            Log::channel('reports-controlling-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function exportFullMarginList(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->exportFullMarginList($request->all());
        } catch (\Exception $exception) {
            Log::channel('reports-controlling-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function exportFullShopList(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->exportFullShopList($request->all());
        } catch (\Exception $exception) {
            Log::channel('reports-controlling-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
