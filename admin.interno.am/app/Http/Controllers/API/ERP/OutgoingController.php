<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Outgoing\OutgoingFetchByFieldRequest;
use App\Http\Requests\ERP\Outgoing\OutgoingFetchRequest;
use App\Http\Requests\ERP\Outgoing\OutgoingInsertRequest;
use App\Http\Requests\ERP\Outgoing\OutgoingUpdateRequest;
use App\Services\ERP\Outgoing\OutgoingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OutgoingController extends Controller
{
    private OutgoingService $service;

    public function __construct(OutgoingService $service)
    {
        $this->service = $service;
    }

    public function fetchIndexParams(): JsonResponse
    {
        try {
            return $this->service->fetchIndexParams();
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetch(OutgoingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(OutgoingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(OutgoingInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(OutgoingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            return $this->service->delete((int) $id);
        } catch (\Exception $exception) {
            Log::channel('outgoings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
