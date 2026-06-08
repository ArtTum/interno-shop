<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\General\GeneralInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralController extends Controller
{
    private GeneralInfoService $service;

    public function __construct(GeneralInfoService $service)
    {
        $this->service = $service;
    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            return $this->service->fetch();
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchVendorsForSwitch(Request $request): JsonResponse
    {
        try {
            $vendorKey = $request->header('VendorKey');

            if (!$vendorKey) {
                $vendorKey = $request->input('vendor_key');
            }

            return $this->service->fetchVendorsForSwitch($vendorKey);
        } catch (\Exception $exception) {
            Log::channel('erp-general-errors')->error('fetchVendorsForSwitch', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
