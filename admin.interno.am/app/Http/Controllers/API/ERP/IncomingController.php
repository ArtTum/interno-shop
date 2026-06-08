<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Incoming\IncomingFetchByFieldRequest;
use App\Http\Requests\ERP\Incoming\IncomingFetchRequest;
use App\Http\Requests\ERP\Incoming\IncomingInsertRequest;
use App\Http\Requests\ERP\Incoming\IncomingUpdateRequest;
use App\Services\ERP\Incoming\IncomingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IncomingController extends Controller
{
    private IncomingService $service;

    public function __construct(IncomingService $service)
    {
        $this->service = $service;
    }

    public function fetch(IncomingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function fetchByField(IncomingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function insert(IncomingInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function update(IncomingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            return $this->service->delete((int) $id);
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function export(IncomingFetchRequest $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->export($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function fetchStats(IncomingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchStats($request->validated());
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function fetchIndexParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchIndexParams();
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function fetchParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchParams();
        } catch (\Exception $exception) {
            Log::channel('incomings-errors')->error('Failed', ['error' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

}
