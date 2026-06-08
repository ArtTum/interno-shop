<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Offer\AutocompleteRequest;
use App\Http\Requests\ERP\Offer\OfferFetchByFieldRequest;
use App\Http\Requests\ERP\Offer\OfferFetchRequest;
use App\Http\Requests\ERP\Offer\OfferInsertRequest;
use App\Http\Requests\ERP\Offer\OfferUpdateRequest;
use App\Services\ERP\Marketing\Offer\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class OfferController extends Controller
{
    private OfferService $service;

    public function __construct(OfferService $service)
    {
        $this->service = $service;
    }

    public function fetch(OfferFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(OfferFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(OfferInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(OfferUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('offers-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function autocomplete(AutocompleteRequest $request): JsonResponse
    {
        try {
            return $this->service->autocomplete($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
