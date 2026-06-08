<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\AllCountry\AllCountryFetchByFieldRequest;
use App\Http\Requests\ERP\AllCountry\AllCountryFetchRequest;
use App\Http\Requests\ERP\AllCountry\AllCountryInsertRequest;
use App\Http\Requests\ERP\AllCountry\AllCountryUpdateRequest;
use App\Services\ERP\Vendor\Countries\AllCountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AllCountryController extends Controller
{
    private AllCountryService $service;

    public function __construct(AllCountryService $service)
    {
        $this->service = $service;
    }

    public function fetch(AllCountryFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('all-countries-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(AllCountryFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('all-countries-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function store(AllCountryInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->store($request->validated());
        } catch (\Exception $exception) {
            Log::channel('all-countries-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(AllCountryUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('all-countries-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            return $this->service->delete((int) $id);
        } catch (\Exception $exception) {
            Log::channel('all-countries-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
