<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Language\LanguageFetchByFieldRequest;
use App\Http\Requests\ERP\Language\LanguageFetchRequest;
use App\Http\Requests\ERP\Language\LanguageInsertRequest;
use App\Http\Requests\ERP\Language\LanguageUpdateRequest;
use App\Services\ERP\Settings\Language\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    private LanguageService $service;

    public function __construct(LanguageService $service)
    {
        $this->service = $service;
    }

    public function fetch(LanguageFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('languages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(LanguageFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('languages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(LanguageInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('languages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(LanguageUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('languages-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('languages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
