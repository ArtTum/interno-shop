<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Item\ItemAutocompleteRequest;
use App\Http\Requests\ERP\Item\ItemFindForReshipmentRequest;
use App\Http\Requests\ERP\Item\ItemUpdateStatusRequest;
use App\Services\ERP\Warehouse\Item\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemController extends Controller
{
    private ItemService $service;

    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->all());
        } catch (\Exception $exception) {
            Log::channel('categories-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            return $this->service->upload($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('items-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updateStatus(ItemUpdateStatusRequest $request): JsonResponse
    {
        try {
            return $this->service->updateStatus($request->validated());
        } catch (\Exception $exception) {
            Log::channel('items-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function findForReshipment(ItemFindForReshipmentRequest $request): JsonResponse
    {
        try {
            return $this->service->findForReshipment($request->validated());
        } catch (\Exception $exception) {
            Log::channel('items-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('items-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function autocomplete(ItemAutocompleteRequest $request): JsonResponse
    {
        try {
            return $this->service->autocomplete($request->validated());
        } catch (\Exception $exception) {
            Log::channel('items-errors')->error('Failed', ['error' => $exception]);

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
            return $this->service->delete((int)$id);
        } catch (\Exception $exception) {
            Log::channel('item-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
