<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Trash\TrashFetchRequest;
use App\Services\ERP\Trash\TrashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TrashController extends Controller
{
    private TrashService $service;

    public function __construct(TrashService $service)
    {
        $this->service = $service;
    }

    public function fetch(TrashFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::error('Trash fetch failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            return $this->service->restore((int) $id);
        } catch (\Exception $exception) {
            Log::error('Trash restore failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }

    public function forceDelete($id): JsonResponse
    {
        try {
            return $this->service->forceDelete((int) $id);
        } catch (\Exception $exception) {
            Log::error('Trash force delete failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], (((int)$exception->getCode() >= 100 && (int)$exception->getCode() < 600) ? (int)$exception->getCode() : 400));
        }
    }
}