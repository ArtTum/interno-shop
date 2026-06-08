<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Users\Segment\CustomerSegmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerSegmentController extends Controller
{
    private CustomerSegmentService $service;

    public function __construct(CustomerSegmentService $service)
    {
        $this->service = $service;
    }

    public function fetch(Request $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        try {
            return $this->service->fetch($request->all());
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(Request $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        try {
            return $this->service->fetchByField($request->all());
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(Request $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        try {
            $request['criteria'] = json_decode($request->input('criteria'), true);
            return $this->service->insert($request->all());
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(Request $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        try {
            $request['criteria'] = json_decode($request->input('criteria'), true);
            return $this->service->update($request->all());
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete($id): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        try {
            return $this->service->delete((int)$id);
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

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
            return $this->service->fetchParams();
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->export($request->all());
        } catch (\Exception $exception) {
            Log::channel('customers-segments-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
