<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Report\Customers\CustomersService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomersReportController extends Controller
{
    private CustomersService $service;

    public function __construct(CustomersService $service)
    {
        $this->service = $service;
    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->all());
        } catch (\Exception $exception) {
            Log::channel('reports-customers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }


    public function exportCustomersFullList(Request $request): BinaryFileResponse|JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        try {
            return $this->service->exportCustomersFullList($request->all());
        } catch (\Exception $exception) {
            Log::channel('reports-customers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
