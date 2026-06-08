<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Tools\ShippingCostsUploader\ShippingCostsUploaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingCostsUploaderController extends Controller
{
    private ShippingCostsUploaderService $service;

    public function __construct(ShippingCostsUploaderService $service)
    {
        $this->service = $service;
    }

    public function process(Request $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        try {
            return $this->service->process($request->all());
        } catch (\Exception $exception) {
            Log::channel('shipping-costs-uploader-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
